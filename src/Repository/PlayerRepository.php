<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;

interface PlayerRepository
{
    public function find(int $id): ?Player;

    public function findByNameAndWorld(string $playerName, World $world): ?Player;

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
    public function findByWorldAndNetWorth(World $world, $limit = 10): array;

    public function remove(Player $player): void;

    public function save(Player $player): void;
}
