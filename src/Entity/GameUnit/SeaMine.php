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
use FrankProjects\UltimateWarfare\Entity\BattleStats\SeaBattleStats;

final class SeaMine extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Sea Mine',
            nameMulti: 'Sea Mines',
            rowName: 'sea_mine',
            image: 'gu_sea_mine.jpg',
            netWorth: 0,
            timestamp: 7200,
            description: 'Sea mines damage ships attacking this region. Each level lays an '
                . 'additional layer of mines, increasing both their sea defence and durability.',
            gameUnitCategory: GameUnitCategory::DEFENSE_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 3500, food: 0, wood: 0, steel: 1),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 10,
                armor: 1,
                seaBattleStats: new SeaBattleStats(defence: 5, defenceSpeed: 500),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::SEA_MINE;
    }
}
