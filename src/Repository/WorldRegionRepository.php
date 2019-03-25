<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface WorldRegionRepository
{
    /**
     * @param int $id
     * @return WorldRegion|null
     */
    public function find(int $id): ?WorldRegion;

    /**
     * @param World $world
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldAndPlayer(World $world, ?Player $player): array;

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     * @return WorldRegion|null
     */
    public function findByWorldXY(World $world, int $x, int $y): ?WorldRegion;

    /**
     * @param WorldRegion $worldRegion
     * @return array
     */
    public function getWorldGameUnitSumByWorldRegion(WorldRegion $worldRegion): array;

    /**
     * @param int $id
     * @param Player $player
     * @return WorldRegion|null
     */
    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    /**
     * @param int $id
     * @param Player $player
     * @return WorldRegion|null
     */
    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    /**
     * @param WorldRegion $worldRegion
     */
    public function save(WorldRegion $worldRegion): void;
}
