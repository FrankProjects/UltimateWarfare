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
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor\ConstructionProcessor;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor\ResearchProcessor;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;

final class GameEngine
{
    private const int SYNC_INTERVAL_SECONDS = 60;

    private ConstructionProcessor $constructionProcessor;
    private ResearchProcessor $researchProcessor;
    private ConstructionRepository $constructionRepository;
    private ResearchPlayerRepository $researchPlayerRepository;
    private PlayerRepository $playerRepository;
    private IncomeUpdaterService $incomeUpdaterService;
    private ResourceAccrualCalculator $resourceAccrualCalculator;
    private EntityManagerInterface $entityManager;
    private int $constructionTimeOverride;
    private int $researchTimeOverride;

    public function __construct(
        ConstructionProcessor $constructionProcessor,
        ResearchProcessor $researchProcessor,
        ConstructionRepository $constructionRepository,
        ResearchPlayerRepository $researchPlayerRepository,
        PlayerRepository $playerRepository,
        IncomeUpdaterService $incomeUpdaterService,
        ResourceAccrualCalculator $resourceAccrualCalculator,
        EntityManagerInterface $entityManager,
        int $constructionTimeOverride,
        int $researchTimeOverride
    ) {
        $this->constructionProcessor = $constructionProcessor;
        $this->researchProcessor = $researchProcessor;
        $this->constructionRepository = $constructionRepository;
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->playerRepository = $playerRepository;
        $this->incomeUpdaterService = $incomeUpdaterService;
        $this->resourceAccrualCalculator = $resourceAccrualCalculator;
        $this->entityManager = $entityManager;
        $this->constructionTimeOverride = $constructionTimeOverride;
        $this->researchTimeOverride = $researchTimeOverride;
    }

    public function run(?Player $player): void
    {
        $timestamp = time();

        $players = $this->getPlayersWithCompletedQueueItems($timestamp);

        // Accrual is exact however often it runs, so skip the locking write when nothing is due and it ran recently
        if ($player !== null && $player->getTimestampUpdate() + self::SYNC_INTERVAL_SECONDS <= $timestamp) {
            $players[$player->getId()] = $player;
        }

        foreach ($players as $playerToSync) {
            $this->syncPlayer($playerToSync, $timestamp);
        }

        $this->processPopulationGrowth($timestamp);
    }

    /**
     * Brings a player's state up to $timestamp: completes due queue items in chronological order and
     * accrues resources at the rate that was in effect during each interval.
     *
     * The player is refreshed from the database under a row lock, so unflushed changes to the player entity
     * are discarded. Inside an outer transaction (every POST request, see GameEngineSubscriber) the commit
     * below is nested, so the lock is held until the request's transaction commits: call this before
     * changing another player's resources or income/upkeep.
     */
    public function syncPlayer(Player $player, int $timestamp): void
    {
        $this->entityManager->beginTransaction();

        try {
            // Lock the player row so concurrent requests can't accrue or complete queue items twice
            $this->entityManager->refresh($player, LockMode::PESSIMISTIC_WRITE);

            foreach ($this->getCompletedQueueItems($player, $timestamp) as [$completedAt, $queueItem]) {
                // Accrue at the old rate up to the moment the queue item completed
                $this->resourceAccrualCalculator->accrue($player, min($completedAt, $timestamp));

                if ($queueItem instanceof Construction) {
                    if (!$this->constructionProcessor->complete($queueItem)) {
                        continue;
                    }
                } else {
                    $this->researchProcessor->complete($queueItem);
                }

                $this->incomeUpdaterService->updateIncomeForPlayer($player);
            }

            $this->resourceAccrualCalculator->accrue($player, $timestamp);
            $this->playerRepository->save($player);

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
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
            $queueItems[] = [$construction->getTimestamp() + $construction->getDuration(), $construction];
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

    public function processPopulationGrowth(int $timestamp): void
    {
        /**
         * //income
         * $constant_income_pop = 0.1;
         *
         * //units
         * $constant_house_pop = 500;
         * $constant_house_pop_growth = 10;
         *
         * $income_pop_increase = 0;
         *
         * $query = "SELECT id, pop, (SELECT ifnull(amount,0) FROM world_region_units WHERE region_id = world_region.id
         * AND unit_id = 5) as house FROM world_region WHERE owner = $player_id";
         * $result = $db->query($query);
         * if ($db->num_rows($result) > 0) {
         * while ($arr = $db->fetch_assoc($result)) {
         * //Set to 0 if NULL is found
         * $arr['house'] = $arr['house'] == "" ? "0" : "".$arr['house']."";
         *
         * if(round($arr['pop']) < ($arr['house'] * $constant_house_pop)){
         * //update pop
         * $pop_growth = (($constant_house_pop_growth / 3600) * $arr['house']) * $time_diff;
         * if(($pop_growth + $arr['pop']) > ($arr['house'] * $constant_house_pop)){
         * $pop_growth = ($arr['house'] * $constant_house_pop) - $arr['pop'];
         * }
         * $pop_growth = round($pop_growth, 3);
         * //update region pop
         * $db->query("UPDATE world_region SET pop = pop + $pop_growth WHERE id = ".$arr['id']."");
         * $income_pop_increase = round($income_pop_increase + ($pop_growth * $constant_income_pop), 3);
         * }
         * }
         * }
         * //update player income
         * $db->query("UPDATE player SET income_cash = income_cash + $income_pop_increase WHERE id = $player_id");
         */
    }
}
