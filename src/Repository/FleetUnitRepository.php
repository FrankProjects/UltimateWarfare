<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;

interface FleetUnitRepository
{
    /**
     * @return array<int, int> Total amount per game unit id, over all fleets of the player
     */
    public function getAmountSumByPlayer(Player $player): array;

    public function remove(FleetUnit $fleetUnit): void;

    public function save(FleetUnit $fleetUnit): void;
}
