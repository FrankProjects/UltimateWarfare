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

final class Destroyer extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Destroyer',
            nameMulti: 'Destroyers',
            rowName: 'destroyer',
            image: 'gu_destroyer.jpg',
            netWorth: 300,
            timestamp: 36000,
            description: '',
            gameUnitCategory: GameUnitCategory::NAVAL_UNITS,
            behaviorClass: NavalUnitBehavior::class,
            cost: new Cost(cash: 25000, food: 5000, wood: 1000, steel: 1500),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 300, food: 80, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 800,
                armor: 10,
                travelSpeed: 100,
                airBattleStats: new AirBattleStats(defence: 1500, defenceSpeed: 150),
                seaBattleStats: new SeaBattleStats(attack: 5000, attackSpeed: 150, defence: 5500, defenceSpeed: 160),
                groundBattleStats: new GroundBattleStats(attack: 600, attackSpeed: 40),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::DESTROYER;
    }
}
