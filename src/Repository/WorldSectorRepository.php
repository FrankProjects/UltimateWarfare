<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldSector;

interface WorldSectorRepository
{
    public function findByIdAndWorld(int $id, World $world): ?WorldSector;

    public function findByWorldXY(World $world, int $x, int $y): ?WorldSector;

    public function save(WorldSector $worldSector): void;

    public function refresh(WorldSector $worldSector): void;
}
