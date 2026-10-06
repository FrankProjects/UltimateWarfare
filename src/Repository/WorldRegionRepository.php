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

    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    /**
     * @return WorldRegion[]
     */
    public function findAdjacentRegions(int $x, int $y, World $world): array;

    public function save(WorldRegion $worldRegion): void;
}
