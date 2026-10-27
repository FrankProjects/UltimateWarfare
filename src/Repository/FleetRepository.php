<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;

interface FleetRepository
{
    public function findByIdAndPlayer(int $id, Player $player): ?Fleet;

    /**
     * @param Player $player
     * @return Fleet[]
     */
    public function findByPlayer(Player $player): array;

    /**
     * Fleets of every other player in the world
     *
     * @return Fleet[]
     */
    public function findByWorldExcludingPlayer(World $world, Player $player): array;

    public function remove(Fleet $fleet): void;

    public function save(Fleet $fleet): void;
}
