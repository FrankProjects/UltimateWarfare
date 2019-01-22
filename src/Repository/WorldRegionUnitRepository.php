<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;

interface WorldRegionUnitRepository
{
    /**
     * @param int $id
     * @return WorldRegionUnit|null
     */
    public function find(int $id): ?WorldRegionUnit;

    /**
     * @param Player $player
     * @return array
     */
    public function findAmountAndNetworthByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @param GameUnitType[] $gameUnitTypes
     * @return array
     */
    public function getGameUnitSumByPlayerAndGameUnitTypes(Player $player, array $gameUnitTypes): array;

    /**
     * @param WorldRegionUnit $worldRegionUnit
     */
    public function remove(WorldRegionUnit $worldRegionUnit): void;

    /**
     * @param WorldRegionUnit $worldRegionUnit
     */
    public function save(WorldRegionUnit $worldRegionUnit): void;
}
