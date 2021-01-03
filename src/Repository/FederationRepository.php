<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\World;

interface FederationRepository
{
    public function findByIdAndWorld(int $id, World $world): ?Federation;

    public function findByNameAndWorld(string $name, World $world): ?Federation;

    /**
     * @param World $world
     * @return Federation[]
     */
    public function findByWorldSortedByRegion(World $world): array;

    public function remove(Federation $federation): void;

    public function save(Federation $federation): void;
}
