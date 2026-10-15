<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;

/**
 * Every House keeps a population of 500 in its region, which grows by one person per House every tick
 * (10 per House per hour). A region never loses population when it drops below its capacity, only when the
 * player starves (see StarvationUpdaterService).
 *
 * Population grows in fixed ticks on the Unix epoch (not continuously), so the income it brings in (see
 * IncomeCalculator) only changes at a tick: between ticks resources accrue at a constant rate, exactly as
 * between completing queue items. PlayerStateUpdaterService grows the population at every tick it passes.
 */
final class PopulationUpdaterService
{
    public const int TICK_SECONDS = 360;
    public const int POPULATION_PER_HOUSE = 500;
    private const int GROWTH_PER_HOUSE_PER_TICK = 1;

    private WorldRegionRepository $worldRegionRepository;
    private PlayerRepository $playerRepository;

    public function __construct(WorldRegionRepository $worldRegionRepository, PlayerRepository $playerRepository)
    {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * Grows the population of the player's regions by one tick
     *
     * @return bool Whether any region grew (false means it won't grow until the player gets more Houses)
     */
    public function updatePopulationForPlayer(Player $player): bool
    {
        $worldRegions = [];
        $belowCapacity = $this->worldRegionRepository->findBelowPopulationCapacityByPlayer(
            $player,
            self::POPULATION_PER_HOUSE
        );
        foreach ($belowCapacity as [$worldRegion, $houses]) {
            $worldRegion->setPopulation(min(
                $houses * self::POPULATION_PER_HOUSE,
                $worldRegion->getPopulation() + $houses * self::GROWTH_PER_HOUSE_PER_TICK
            ));
            $worldRegions[] = $worldRegion;
        }

        if ($worldRegions === []) {
            return false;
        }

        $this->worldRegionRepository->saveAll($worldRegions);

        return true;
    }

    public function isPopulationGrowingForPlayer(Player $player): bool
    {
        return $this->worldRegionRepository->findBelowPopulationCapacityByPlayer(
            $player,
            self::POPULATION_PER_HOUSE
        ) !== [];
    }

    /**
     * Players whose population grows at a tick after their state was last updated, up to $timestamp
     *
     * @return Player[]
     */
    public function getPlayersWithPopulationGrowthDue(int $timestamp): array
    {
        return $this->playerRepository->findWithPopulationBelowCapacityUpdatedBefore(
            $this->getLastTickTimestamp($timestamp),
            self::POPULATION_PER_HOUSE
        );
    }

    /**
     * The first tick after $timestamp
     */
    public function getNextTickTimestamp(int $timestamp): int
    {
        return $this->getLastTickTimestamp($timestamp) + self::TICK_SECONDS;
    }

    /**
     * The last tick at or before $timestamp
     */
    public function getLastTickTimestamp(int $timestamp): int
    {
        return intdiv($timestamp, self::TICK_SECONDS) * self::TICK_SECONDS;
    }
}
