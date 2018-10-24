<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Federation;

interface FederationRepositoryInterface
{
    /**
     * @param int $id
     * @return Federation|null
     */
    public function find(int $id): ?Federation;

    /**
     * @param Federation $federation
     */
    public function save(Federation $federation): void;
}
