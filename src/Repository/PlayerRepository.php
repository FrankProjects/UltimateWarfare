<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;

interface PlayerRepository
{
    /**
     * @param int $id
     * @return Player|null
     */
    public function find(int $id): ?Player;

    /**
     * @param Player $player
     */
    public function save(Player $player): void;

    /**
     * @param World $world
     * @param int $limit
     * @return Player[]
     */
    public function findByWorldAndRegions(World $world, $limit = 10): array;

    /**
     * @param World $world
     * @param int $limit
     * @return Player[]
     */
    public function findByWorldAndNetworth(World $world, $limit = 10): array;
}
