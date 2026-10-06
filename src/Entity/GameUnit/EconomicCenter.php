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

final class EconomicCenter extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Economic Center',
            nameMulti: 'Economic Centers',
            rowName: 'economic',
            image: 'gu_economic_center.jpg',
            netWorth: 1,
            timestamp: 7200,
            description: 'An Economic Center makes 15 cash per hour.',
            gameUnitCategory: GameUnitCategory::BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 100, food: 0, wood: 5, steel: 1),
            income: new Income(cash: 15, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::ECONOMIC_CENTER;
    }
}
