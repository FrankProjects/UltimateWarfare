<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;

/**
 * A player without food who needs more food than they produce starves at every population tick (see
 * PopulationUpdaterService): their regions lose 3.5% of their population, compounding to 30% per hour. Losses are
 * rounded up, so small populations run out as well. A starving population doesn't grow. Starvation only affects
 * population, not units.
 */
final class StarvationUpdaterService
{
    private const int POPULATION_LOSS_PER_100_000 = 3_505;

    private WorldRegionRepository $worldRegionRepository;
    private PlayerRepository $playerRepository;
    private PopulationUpdaterService $populationUpdaterService;

    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        PlayerRepository $playerRepository,
        PopulationUpdaterService $populationUpdaterService
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->playerRepository = $playerRepository;
        $this->populationUpdaterService = $populationUpdaterService;
    }

    public function isStarving(Player $player): bool
    {
        return $player->getResources()->getFood() === 0 && $this->hasFoodShortage($player);
    }

    /**
     * Whether the player needs more food than they produce, so they starve once their food runs out
     */
    public function hasFoodShortage(Player $player): bool
    {
        return $this->getNetFood($player) < 0;
    }

    /**
     * Starves the player's population for one tick
     *
     * @return bool Whether any population was lost (false means there is none left to lose)
     */
    public function updateStarvationForPlayer(Player $player): bool
    {
        $worldRegions = $this->worldRegionRepository->findWithPopulationByPlayer($player);
        if ($worldRegions === []) {
            return false;
        }

        foreach ($worldRegions as $worldRegion) {
            $population = $worldRegion->getPopulation();
            // Rounded up
            $loss = intdiv($population * self::POPULATION_LOSS_PER_100_000 + 99_999, 100_000);
            $worldRegion->setPopulation($population - $loss);
        }

        $this->worldRegionRepository->saveAll($worldRegions);

        return true;
    }

    /**
     * When the player first starves at a tick after their last update, assuming their income/upkeep doesn't
     * change, or null if their food doesn't run out. Approximate: the tick may turn out to be one too early.
     */
    public function getNextStarvationTimestamp(Player $player): ?int
    {
        if (!$this->hasFoodShortage($player)) {
            return null;
        }
        $netFood = $this->getNetFood($player);

        $timestampUpdate = $player->getTimestampUpdate();
        $foodRunsOutAt = $timestampUpdate + intdiv($player->getResources()->getFood() * 3600, -$netFood);

        return $this->populationUpdaterService->getNextTickTimestamp(max($foodRunsOutAt - 1, $timestampUpdate));
    }

    /**
     * Players without food at a tick after their state was last updated, up to $timestamp
     *
     * @return Player[]
     */
    public function getPlayersWithStarvationDue(int $timestamp): array
    {
        $tickTimestamp = $this->populationUpdaterService->getLastTickTimestamp($timestamp);
        $players = $this->playerRepository->findWithFoodShortageUpdatedBefore($tickTimestamp);

        return array_values(array_filter(
            $players,
            fn (Player $player): bool => $this->hasFoodRunOutAt($player, $tickTimestamp)
        ));
    }

    /**
     * Whether the player's food has run out at $timestamp, at the income/upkeep rate since their last update
     */
    private function hasFoodRunOutAt(Player $player, int $timestamp): bool
    {
        $secondsSinceUpdate = $timestamp - $player->getTimestampUpdate();

        return $player->getResources()->getFood() * 3600 + $this->getNetFood($player) * $secondsSinceUpdate <= 0;
    }

    private function getNetFood(Player $player): int
    {
        return $player->getIncome()->getFood() - $player->getUpkeep()->getFood();
    }
}
