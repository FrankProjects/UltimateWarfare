<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameResource;

interface GameResourceRepository
{
    /**
     * @param int $id
     * @return GameResource|null
     */
    public function find(int $id): ?GameResource;

    /**
     * @return GameResource[]
     */
    public function findAll(): array;
}
