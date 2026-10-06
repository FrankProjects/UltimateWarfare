<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\GameUnit;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\BattleStats;
use FrankProjects\UltimateWarfare\Entity\GameResources\Cost;
use FrankProjects\UltimateWarfare\Entity\GameResources\Income;
use FrankProjects\UltimateWarfare\Entity\GameResources\Upkeep;

final class Harbor extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Harbor',
            nameMulti: 'Harbors',
            rowName: 'harbor',
            image: 'gu_harbor.jpg',
            netWorth: 15,
            timestamp: 28800,
            description: 'An harbor can send 1 ship to your neighbour countries '
                . 'and help them defending when they are under attack. Each upgrade level '
                . 'builds naval units in this region 5% faster (up to 45% at level 10).',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 25000, food: 0, wood: 600, steel: 150),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 7000, armor: 2),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::HARBOR;
    }
}
