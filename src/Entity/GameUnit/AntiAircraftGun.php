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

final class AntiAircraftGun extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Anti Aircraft Gun',
            nameMulti: 'Anti Aircraft Guns',
            rowName: 'anti_aircraft_gun',
            image: 'gu_anti_aircraft_gun.jpg',
            netWorth: 0,
            timestamp: 14400,
            description: 'Anti aircraft guns defend this region against air, sea and ground '
                . 'attacks. Each level adds more guns, increasing both defence and durability.',
            gameUnitCategory: GameUnitCategory::DEFENSE_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 25000, food: 0, wood: 150, steel: 350),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 1500,
                armor: 2,
                airBattleStats: new AirBattleStats(defence: 130, defenceSpeed: 900),
                seaBattleStats: new SeaBattleStats(defence: 80, defenceSpeed: 70),
                groundBattleStats: new GroundBattleStats(defence: 70, defenceSpeed: 40),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::ANTI_AIRCRAFT_GUN;
    }
}
