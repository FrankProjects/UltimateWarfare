<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\FederationNews;

interface FederationNewsRepository
{
    /**
     * @param Federation $federation
     * @return FederationNews[]
     */
    public function findByFederationSortedByTimestamp(Federation $federation): array;

    public function remove(FederationNews $federationNews): void;

    public function save(FederationNews $federationNews): void;
}
