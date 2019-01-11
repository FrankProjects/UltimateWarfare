<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\FleetUnit;

interface FleetUnitRepository
{
    /**
     * @param FleetUnit $fleetUnit
     */
    public function remove(FleetUnit $fleetUnit): void;

    /**
     * @param FleetUnit $fleetUnit
     */
    public function save(FleetUnit $fleetUnit): void;
}
