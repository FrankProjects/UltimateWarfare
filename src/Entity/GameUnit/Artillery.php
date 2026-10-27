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

final class Artillery extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Artillery',
            nameMulti: 'Artillery',
            rowName: 'artillery',
            image: 'gu_artillery.jpg',
            netWorth: 10,
            timestamp: 14400,
            description: 'Artillery bombard enemy bunkers before the battle, '
                . 'ignoring their armor, before joining the ground fight.',
            gameUnitCategory: GameUnitCategory::TROOPS,
            behaviorClass: null,
            cost: new Cost(cash: 4000, food: 500, wood: 20, steel: 40),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 10, food: 3, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 70,
                armor: 3,
                travelSpeed: 200,
                groundBattleStats: new GroundBattleStats(attack: 300, attackSpeed: 90, defence: 150, defenceSpeed: 90),
            ),
            population: 4,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::ARTILLERY;
    }
}
