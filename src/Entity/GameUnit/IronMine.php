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

final class IronMine extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Iron Mine',
            nameMulti: 'Iron Mines',
            rowName: 'iron_mine',
            image: 'gu_iron_mine.jpg',
            netWorth: 1,
            timestamp: 18000,
            description: 'An Iron Mine makes 1 Steel per hour.',
            gameUnitCategory: GameUnitCategory::BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 1500, food: 0, wood: 50, steel: 0),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 1),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::IRON_MINE;
    }
}
