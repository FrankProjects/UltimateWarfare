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

final class Tank extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Tank',
            nameMulti: 'Tanks',
            rowName: 'tank',
            image: 'gu_tank.jpg',
            netWorth: 10,
            timestamp: 10800,
            description: '',
            gameUnitCategory: GameUnitCategory::TROOPS,
            behaviorClass: null,
            cost: new Cost(cash: 5000, food: 1000, wood: 20, steel: 55),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 15, food: 5, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 1500,
                armor: 10,
                travelSpeed: 200,
                groundBattleStats: new GroundBattleStats(
                    attack: 50,
                    attackSpeed: 130,
                    defence: 55,
                    defenceSpeed: 135,
                ),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::TANK;
    }
}
