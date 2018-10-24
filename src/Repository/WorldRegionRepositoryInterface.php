<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface WorldRegionRepositoryInterface
{
    /**
     * @param int $id
     * @return WorldRegion|null
     */
    public function find(int $id): ?WorldRegion;

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
