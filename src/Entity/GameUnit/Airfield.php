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

final class Airfield extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Airfield',
            nameMulti: 'Airfields',
            rowName: 'airfield',
            image: 'gu_airfield.jpg',
            netWorth: 20,
            timestamp: 14400,
            description: 'An airfield can send 10 planes to your neighbour countries '
                . 'and help them defending when they are under attack. Each upgrade level '
                . 'builds air units in this region 5% faster (up to 45% at level 10).',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 15000, food: 0, wood: 50, steel: 75),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 7000, armor: 2),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::AIRFIELD;
    }
}
