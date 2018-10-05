<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface WorldRegionRepositoryInterface
{
    /**
     * @param int $worldRegionId
     * @return WorldRegion|null
     */
    public function find(int $worldRegionId): ?WorldRegion;

    /**
     * @param WorldRegion $worldRegion
     */
    public function save(WorldRegion $worldRegion): void;
}
