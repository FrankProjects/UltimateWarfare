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
use FrankProjects\UltimateWarfare\Entity\BattleStats\GroundBattleStats;

final class Soldier extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Soldier',
            nameMulti: 'Soldiers',
            rowName: 'soldier',
            image: 'gu_soldier.jpg',
            netWorth: 1,
            timestamp: 1800,
            description: '',
            gameUnitCategory: GameUnitCategory::TROOPS,
            behaviorClass: null,
            cost: new Cost(cash: 500, food: 50, wood: 1, steel: 5),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 1, food: 1, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 50,
                armor: 1,
                travelSpeed: 100,
                groundBattleStats: new GroundBattleStats(
                    attack: 10,
                    attackSpeed: 100,
                    defence: 12,
                    defenceSpeed: 110,
                ),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::SOLDIER;
    }
}
