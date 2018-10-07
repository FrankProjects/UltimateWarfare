<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

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
     */
    public function save(WorldRegion $worldRegion): void;
}
