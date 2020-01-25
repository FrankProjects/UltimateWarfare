<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;

interface WorldRegionUnitRepository
{
    public function find(int $id): ?WorldRegionUnit;

    public function findAmountAndNetworthByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @param GameUnitType[] $gameUnitTypes
     * @return array
     */
    public function getGameUnitSumByPlayerAndGameUnitTypes(Player $player, array $gameUnitTypes): array;

    public function remove(WorldRegionUnit $worldRegionUnit): void;

    public function save(WorldRegionUnit $worldRegionUnit): void;
}
