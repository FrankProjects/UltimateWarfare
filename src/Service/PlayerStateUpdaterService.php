<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Service\PlayerState\ConstructionCompleter;
use FrankProjects\UltimateWarfare\Service\PlayerState\ResearchCompleter;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;

/**
 * A player's state (resources, units, research) is stored as it was at Player::timestampUpdate. This service
 * brings it up to a later moment: queued construction and research complete at their completion time and the
 * population grows (or the player starves) at every population tick, in chronological order, and resources
 * accrue at the income/upkeep rate that applied in each interval.
 * The result is the same however often, and however late, it runs.
 */
final class PlayerStateUpdaterService
{
    private ConstructionCompleter $constructionCompleter;
    private ResearchCompleter $researchCompleter;
    private ConstructionRepository $constructionRepository;
    private ResearchPlayerRepository $researchPlayerRepository;
    private PlayerRepository $playerRepository;
    private IncomeUpdaterService $incomeUpdaterService;
    private PopulationUpdaterService $populationUpdaterService;
    private StarvationUpdaterService $starvationUpdaterService;
    private ResourceAccrualCalculator $resourceAccrualCalculator;
    private EntityManagerInterface $entityManager;
    private int $constructionTimeOverride;
    private int $researchTimeOverride;

    public function __construct(
        ConstructionCompleter $constructionCompleter,
        ResearchCompleter $researchCompleter,
        ConstructionRepository $constructionRepository,
        ResearchPlayerRepository $researchPlayerRepository,
        PlayerRepository $playerRepository,
        IncomeUpdaterService $incomeUpdaterService,
        PopulationUpdaterService $populationUpdaterService,
        StarvationUpdaterService $starvationUpdaterService,
        ResourceAccrualCalculator $resourceAccrualCalculator,
        EntityManagerInterface $entityManager,
        int $constructionTimeOverride,
        int $researchTimeOverride
    ) {
        $this->constructionCompleter = $constructionCompleter;
        $this->researchCompleter = $researchCompleter;
        $this->constructionRepository = $constructionRepository;
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->playerRepository = $playerRepository;
        $this->incomeUpdaterService = $incomeUpdaterService;
        $this->populationUpdaterService = $populationUpdaterService;
        $this->starvationUpdaterService = $starvationUpdaterService;
        $this->resourceAccrualCalculator = $resourceAccrualCalculator;
        $this->entityManager = $entityManager;
        $this->constructionTimeOverride = $constructionTimeOverride;
        $this->researchTimeOverride = $researchTimeOverride;
    }

    /**
     * Updates the state of every player with due construction, research, population growth or starvation up to
     * $timestamp,
     * each in its own transaction. Run continuously by the queue worker (game:queue:work).
     */
    public function updateDuePlayers(int $timestamp): void
    {
        $players = $this->getPlayersWithCompletedQueueItems($timestamp);
        foreach ($this->populationUpdaterService->getPlayersWithPopulationGrowthDue($timestamp) as $player) {
            $players[$player->getId()] = $player;
        }
        foreach ($this->starvationUpdaterService->getPlayersWithStarvationDue($timestamp) as $player) {
            $players[$player->getId()] = $player;
        }

        foreach ($players as $player) {
            $this->updateStateForPlayer($player, $timestamp);
        }
    }

    /**
     * Brings a player's stored state up to $timestamp: completes due queue items and grows the population (or
     * starves the player) at every tick in chronological order, and accrues resources at the rate that was in
     * effect during each interval. A queue item completing exactly at a tick completes after the tick.
     *
     * The player is refreshed from the database under a row lock, so unflushed changes to the player entity
     * are discarded. Inside an outer transaction (every POST request, see PlayerRequestSubscriber) the commit
     * below is nested, so the lock is held until the request's transaction commits: call this before
     * changing another player's resources or income/upkeep.
     */
    public function updateStateForPlayer(Player $player, int $timestamp): void
    {
        $this->entityManager->beginTransaction();

        try {
            // Lock the player row so concurrent requests can't accrue or complete queue items twice
            $this->entityManager->refresh($player, LockMode::PESSIMISTIC_WRITE);

            foreach ($this->getCompletedQueueItems($player, $timestamp) as [$completedAt, $queueItem]) {
                $completedAt = min($completedAt, $timestamp);
                $this->processPopulationTicksUntil($player, $completedAt);

                // Accrue at the old rate up to the moment the queue item completed
                $this->resourceAccrualCalculator->accrue($player, $completedAt);

                if ($queueItem instanceof Construction) {
                    if (!$this->constructionCompleter->complete($queueItem)) {
                        continue;
                    }
                } else {
                    $this->researchCompleter->complete($queueItem);
                }

                $this->incomeUpdaterService->updateIncomeForPlayer($player);
            }

            $this->processPopulationTicksUntil($player, $timestamp);
            $this->resourceAccrualCalculator->accrue($player, $timestamp);
            $this->playerRepository->save($player);

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * When the player's income/upkeep rate may next change through a completing queue item, growing population or
     * starvation, or null if none of these is due. Lets clients refresh shortly after, instead of polling.
     */
    public function getNextCompletionTimestamp(Player $player): ?int
    {
        $timestamps = array_filter(
            [
                $this->constructionRepository->getNextCompletionTimestampByPlayer($player),
                $this->researchPlayerRepository->getNextCompletionTimestampByPlayer($player),
                $this->populationUpdaterService->isPopulationGrowingForPlayer($player)
                    ? $this->populationUpdaterService->getNextTickTimestamp($player->getTimestampUpdate())
                    : null,
                $this->starvationUpdaterService->getNextStarvationTimestamp($player),
            ],
            fn(?int $timestamp): bool => $timestamp !== null
        );

        return $timestamps !== [] ? min($timestamps) : null;
    }

    /**
     * Processes every tick after the player's last update up to and including $until, accruing at the old rate up
     * to each tick: a starving player loses population, otherwise the population grows.
     *
     * Stops early once no later tick can change anything: until queue items complete (the next call starts
     * afresh), nothing grows again after a tick nothing grew, and nothing starves after a tick nothing starved.
     */
    private function processPopulationTicksUntil(Player $player, int $until): void
    {
        $canGrow = true;
        $tick = $this->populationUpdaterService->getNextTickTimestamp($player->getTimestampUpdate());
        for (; $tick <= $until; $tick += PopulationUpdaterService::TICK_SECONDS) {
            $this->resourceAccrualCalculator->accrue($player, $tick);

            if ($this->starvationUpdaterService->isStarving($player)) {
                if (!$this->starvationUpdaterService->updateStarvationForPlayer($player)) {
                    return;
                }
                // Regions may be below their capacity again
                $canGrow = true;
            } elseif (!$canGrow || !$this->populationUpdaterService->updatePopulationForPlayer($player)) {
                $canGrow = false;
                if (!$this->starvationUpdaterService->hasFoodShortage($player)) {
                    return;
                }
                // Keep accruing until the food runs out
                continue;
            }

            $this->incomeUpdaterService->updateIncomeForPlayer($player);
        }
    }

    /**
     * @return array<int, Player> Keyed by player id
     */
    private function getPlayersWithCompletedQueueItems(int $timestamp): array
    {
        $players = [];
        $constructions = $this->constructionRepository->getCompletedConstructions(
            $this->getConstructionCutoff($timestamp)
        );
        foreach ($constructions as $construction) {
            $players[$construction->getPlayer()->getId()] = $construction->getPlayer();
        }

        $researches = $this->researchPlayerRepository->getNonActiveCompletedResearch(
            $this->getResearchCutoff($timestamp)
        );
        foreach ($researches as $researchPlayer) {
            $players[$researchPlayer->getPlayer()->getId()] = $researchPlayer->getPlayer();
        }

        return $players;
    }

    /**
     * @return array<int, array{int, Construction|ResearchPlayer}> [completedAt, queueItem], in chronological order
     */
    private function getCompletedQueueItems(Player $player, int $timestamp): array
    {
        $queueItems = [];
        $constructions = $this->constructionRepository->getCompletedConstructionsByPlayer(
            $player,
            $this->getConstructionCutoff($timestamp)
        );
        foreach ($constructions as $construction) {
            $queueItems[] = [$construction->getCompletionTimestamp(), $construction];
        }

        $researches = $this->researchPlayerRepository->getNonActiveCompletedResearchByPlayer(
            $player,
            $this->getResearchCutoff($timestamp)
        );
        foreach ($researches as $researchPlayer) {
            $queueItems[] = [$researchPlayer->getCompletionTimestamp(), $researchPlayer];
        }

        usort($queueItems, fn(array $a, array $b): int => $a[0] <=> $b[0]);

        return $queueItems;
    }

    /**
     * Override construction time for testing purposes: everything in the queue is due
     */
    private function getConstructionCutoff(int $timestamp): int
    {
        return $this->constructionTimeOverride > 0 ? PHP_INT_MAX : $timestamp;
    }

    /**
     * Override research time for testing purposes: everything in the queue is due
     */
    private function getResearchCutoff(int $timestamp): int
    {
        return $this->researchTimeOverride > 0 ? PHP_INT_MAX : $timestamp;
    }
}
