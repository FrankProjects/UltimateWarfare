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
use FrankProjects\UltimateWarfare\Service\GameUnit\Behavior\AirUnitBehavior;

final class Bomber extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Bomber',
            nameMulti: 'Bombers',
            rowName: 'bomber',
            image: 'gu_bomber.jpg',
            netWorth: 130,
            timestamp: 18000,
            description: 'Bombers are tough but slow and poor in a dogfight. Escort them with fighters '
                . 'to protect them from enemy aircraft, and beware of flak, which ignores their armor.',
            gameUnitCategory: GameUnitCategory::AIR_UNITS,
            behaviorClass: AirUnitBehavior::class,
            cost: new Cost(cash: 20000, food: 2000, wood: 200, steel: 600),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 100, food: 20, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 520,
                armor: 4,
                travelSpeed: 500,
                airBattleStats: new AirBattleStats(attack: 100, attackSpeed: 150, defence: 150, defenceSpeed: 150),
                groundBattleStats: new GroundBattleStats(attack: 2500, attackSpeed: 60),
            ),
            population: 4,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::BOMBER;
    }
}
