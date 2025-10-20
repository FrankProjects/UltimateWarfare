<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface ConstructionRepository
{
    public function find(int $id): ?Construction;

    /**
     * @param Player $player
     * @return Construction[]
     */
    public function findByPlayer(Player $player): array;

    /**
     * @return array<int|string, mixed>
     */
    public function getGameUnitConstructionSumByWorldRegion(WorldRegion $worldRegion): array;

    public function getGameUnitConstructionSumByWorldRegionAndType(WorldRegion $worldRegion, GameUnitType $gameUnitType): int;

    /**
     * @return array<int|string, mixed>
     */
    public function getGameUnitConstructionSumByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return Construction[]
     */
    public function findByPlayerAndGameUnitType(Player $player, GameUnitType $gameUnitType): array;

    /**
     * @param int $timestamp
     * @return Construction[]
     */
    public function getCompletedConstructions(int $timestamp): array;

    public function remove(Construction $construction): void;

    public function save(Construction $construction): void;
}
