<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\FederationApplication;
use FrankProjects\UltimateWarfare\Entity\World;

interface FederationApplicationRepository
{
    /**
     * @param int $id
     * @param World $world
     * @return FederationApplication|null
     */
    public function findByIdAndWorld(int $id, World $world): ?FederationApplication;

    /**
     * @param FederationApplication $federationApplication
     */
    public function remove(FederationApplication $federationApplication): void;

    /**
     * @param FederationApplication $federationApplication
     */
    public function save(FederationApplication $federationApplication): void;
}
