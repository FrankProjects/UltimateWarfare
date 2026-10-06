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

final class TrainStation extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Train Station',
            nameMulti: 'Train Stations',
            rowName: 'station',
            image: 'gu_train_station.jpg',
            netWorth: 10,
            timestamp: 10800,
            description: 'An Train station can send 25 soldiers and 1 tank to your neighbour countries '
                . 'and help them defending when they are under attack. Each upgrade level speeds up '
                . 'fleets departing this region by 5% (up to 45% at level 10).',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 10000, food: 0, wood: 500, steel: 750),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 6000, armor: 2),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::TRAIN_STATION;
    }
}
