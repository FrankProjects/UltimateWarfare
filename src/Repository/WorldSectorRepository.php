<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldSector;

interface WorldSectorRepository
{
    /**
     * @param int $id
     * @param World $world
     * @return WorldSector|null
     */
    public function findByIdAndWorld(int $id, World $world): ?WorldSector;
}
