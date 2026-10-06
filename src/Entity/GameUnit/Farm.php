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

final class Farm extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Farm',
            nameMulti: 'Farms',
            rowName: 'farm',
            image: 'gu_farm.jpg',
            netWorth: 1,
            timestamp: 3600,
            description: 'A farm makes 50 food per hour.',
            gameUnitCategory: GameUnitCategory::BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 300, food: 0, wood: 15, steel: 5),
            income: new Income(cash: 0, food: 50, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::FARM;
    }
}
