<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\World;

interface FederationRepository
{
    /**
     * @param int $id
     * @param World $world
     * @return Federation|null
     */
    public function findByIdAndWorld(int $id, World $world): ?Federation;

    /**
     * @param string $name
     * @param World $world
     * @return Federation|null
     */
    public function findByNameAndWorld(string $name, World $world): ?Federation;

    /**
     * @param World $world
     * @return Federation[]
     */
    public function findByWorldSortedByRegion(World $world): array;

    /**
     * @param Federation $federation
     */
    public function remove(Federation $federation): void;

    /**
     * @param Federation $federation
     */
    public function save(Federation $federation): void;
}
