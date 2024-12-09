<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\FederationApplication;
use FrankProjects\UltimateWarfare\Entity\World;

interface FederationApplicationRepository
{
    public function find(int $id): ?FederationApplication;

    public function remove(FederationApplication $federationApplication): void;

    public function save(FederationApplication $federationApplication): void;
}
