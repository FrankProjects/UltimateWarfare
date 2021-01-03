<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface WorldRegionRepository
{
    public function find(int $id): ?WorldRegion;

    /**
     * @param World $world
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldAndPlayer(World $world, ?Player $player): array;

    public function findByWorldXY(World $world, int $x, int $y): ?WorldRegion;

    public function getWorldGameUnitSumByWorldRegion(WorldRegion $worldRegion): array;

    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    public function save(WorldRegion $worldRegion): void;
}
