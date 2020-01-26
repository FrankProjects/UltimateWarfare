<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\FederationApplication;
use FrankProjects\UltimateWarfare\Entity\World;

interface FederationApplicationRepository
{
    public function findByIdAndWorld(int $id, World $world): ?FederationApplication;

    public function remove(FederationApplication $federationApplication): void;

    public function save(FederationApplication $federationApplication): void;
}
