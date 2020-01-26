<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;

interface FleetRepository
{
    public function findByIdAndPlayer(int $id, Player $player): ?Fleet;

    /**
     * @param Player $player
     * @return Fleet[]
     */
    public function findByPlayer(Player $player): array;

    public function remove(Fleet $fleet): void;

    public function save(Fleet $fleet): void;
}
