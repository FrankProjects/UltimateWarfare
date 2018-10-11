<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;

interface WorldRegionUnitRepositoryInterface
{
    /**
     * @param int $id
     * @return WorldRegionUnit|null
     */
    public function find(int $id): ?WorldRegionUnit;

    /**
     * @param WorldRegionUnit $worldRegionUnit
     */
    public function save(WorldRegionUnit $worldRegionUnit): void;
}
