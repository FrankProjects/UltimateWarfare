<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;

interface WorldRegionStackableUnitRepository
{
    public function find(int $id): ?WorldRegionStackableUnit;

    /**
     * @return array<int, array<string, int>>
     */
    public function findAmountAndNetWorthByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @param GameUnitCategory[] $gameUnitCategories
     * @return array<int, int> Total amount per game unit id, over all regions of the player
     */
    public function getGameUnitSumByPlayerAndGameUnitCategories(Player $player, array $gameUnitCategories): array;

    /**
     * @return array<int, int> Total amount per game unit id, over all regions of the player
     */
    public function getAmountSumByPlayer(Player $player): array;

    public function remove(WorldRegionStackableUnit $worldRegionStackableUnit): void;

    public function save(WorldRegionStackableUnit $worldRegionStackableUnit): void;
}
