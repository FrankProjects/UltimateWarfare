<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;

interface FleetRepository
{
    /**
     * @param int $id
     * @return Fleet|null
     */
    public function find(int $id): ?Fleet;

    /**
     * @param Player $player
     * @return Fleet[]
     */
    public function findByPlayer(Player $player): array;

    /**
     * @param Fleet $fleet
     */
    public function remove(Fleet $fleet): void;

    /**
     * @param Fleet $fleet
     */
    public function save(Fleet $fleet): void;
}
