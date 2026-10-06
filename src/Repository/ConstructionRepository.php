<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
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

    public function getGameUnitConstructionSumByWorldRegionAndCategory(
        WorldRegion $worldRegion,
        GameUnitCategory $gameUnitCategory
    ): int;

    /**
     * @return array<int|string, mixed>
     */
    public function getGameUnitConstructionSumByPlayer(Player $player): array;

    /**
     * @return array<int, array<int, int>>
     */
    public function getGameUnitConstructionSumByPlayerGroupedByRegion(Player $player): array;

    /**
     * @param Player $player
     * @param GameUnitCategory $gameUnitCategory
     * @return Construction[]
     */
    public function findByPlayerAndGameUnitCategory(Player $player, GameUnitCategory $gameUnitCategory): array;

    /**
     * @param int $timestamp
     * @return Construction[]
     */
    public function getCompletedConstructions(int $timestamp): array;

    /**
     * @return Construction[]
     */
    public function getAllConstructions(): array;

    public function remove(Construction $construction): void;

    public function save(Construction $construction): void;
}
