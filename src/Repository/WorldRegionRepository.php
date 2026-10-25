<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface WorldRegionRepository
{
    public function find(int $id): ?WorldRegion;

    /**
     * @param World $world
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldAndPlayer(World $world, ?Player $player): array;

    public function findByWorldXY(World $world, int $x, int $y): ?WorldRegion;

    /**
     * Find the player's regions with their stackable units, leveled units and constructions loaded,
     * using one query per collection instead of one query per region per collection
     *
     * @return WorldRegion[]
     */
    public function findByPlayerWithUnitsAndConstructions(Player $player): array;

    /**
     * @return array<int, array<int, int>>
     */
    public function getWorldGameUnitSumByPlayer(Player $player): array;

    /**
     * The player's regions with less population than their Houses can keep, with their amount of Houses
     *
     * @return list<array{WorldRegion, int}>
     */
    public function findBelowPopulationCapacityByPlayer(Player $player, int $populationPerHouse): array;

    /**
     * @return WorldRegion[] The player's regions with a population
     */
    public function findWithPopulationByPlayer(Player $player): array;

    /**
     * Total population of the player's regions; population changes must be flushed first
     */
    public function getPopulationSumByPlayer(Player $player): int;

    /**
     * @return WorldRegion[] Owned water regions without units, leveled units or constructions
     */
    public function findUnoccupiedOwnedWaterRegions(): array;

    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    /**
     * @return WorldRegion[]
     */
    public function findAdjacentRegions(int $x, int $y, World $world): array;

    public function save(WorldRegion $worldRegion): void;

    /**
     * Persist all given regions and flush once, in a single transaction
     *
     * @param WorldRegion[] $worldRegions
     */
    public function saveAll(array $worldRegions): void;

    public function removeForWorld(World $world): void;
}
