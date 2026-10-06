<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;

interface WorldRegionLeveledUnitRepository
{
    public function find(int $id): ?WorldRegionLeveledUnit;

    /**
     * @return array<int, array<string, int>>
     */
    public function findLevelAndNetWorthByPlayer(Player $player): array;

    public function remove(WorldRegionLeveledUnit $worldRegionLeveledUnit): void;

    public function save(WorldRegionLeveledUnit $worldRegionLeveledUnit): void;
}
