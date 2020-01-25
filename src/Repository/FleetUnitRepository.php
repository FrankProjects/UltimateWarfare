<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\FleetUnit;

interface FleetUnitRepository
{
    public function remove(FleetUnit $fleetUnit): void;

    public function save(FleetUnit $fleetUnit): void;
}
