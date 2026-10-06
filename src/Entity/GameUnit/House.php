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

final class House extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'House',
            nameMulti: 'Houses',
            rowName: 'house',
            image: 'gu_house.jpg',
            netWorth: 1,
            timestamp: 900,
            description: 'This house can keep a population of 500.',
            gameUnitCategory: GameUnitCategory::BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 150, food: 50, wood: 5, steel: 1),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::HOUSE;
    }
}
