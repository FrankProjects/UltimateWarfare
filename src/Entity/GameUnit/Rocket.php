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

final class Rocket extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Rocket',
            nameMulti: 'Rockets',
            rowName: 'rocket',
            image: 'gu_rocket.jpg',
            netWorth: 1,
            timestamp: 900,
            description: 'Rockets can be used to bomb an enemy country',
            gameUnitCategory: GameUnitCategory::MISSILES,
            behaviorClass: null,
            cost: new Cost(cash: 200, food: 0, wood: 1, steel: 5),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::ROCKET;
    }
}
