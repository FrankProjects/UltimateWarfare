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
use FrankProjects\UltimateWarfare\Entity\BattleStats\AirBattleStats;
use FrankProjects\UltimateWarfare\Entity\BattleStats\GroundBattleStats;
use FrankProjects\UltimateWarfare\Entity\BattleStats\SeaBattleStats;
use FrankProjects\UltimateWarfare\Service\GameUnit\Behavior\NavalUnitBehavior;

final class PatrolBoat extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Patrol boat',
            nameMulti: 'Patrol boats',
            rowName: 'patrol_boat',
            image: 'gu_patrol_boat.jpg',
            netWorth: 100,
            timestamp: 7200,
            description: '',
            gameUnitCategory: GameUnitCategory::NAVAL_UNITS,
            behaviorClass: NavalUnitBehavior::class,
            cost: new Cost(cash: 4000, food: 1500, wood: 250, steel: 300),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 200, food: 50, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 500,
                armor: 25,
                travelSpeed: 100,
                airBattleStats: new AirBattleStats(defence: 100, defenceSpeed: 50),
                seaBattleStats: new SeaBattleStats(attack: 200, attackSpeed: 150, defence: 230, defenceSpeed: 160),
                groundBattleStats: new GroundBattleStats(attack: 160, attackSpeed: 40),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::PATROL_BOAT;
    }
}
