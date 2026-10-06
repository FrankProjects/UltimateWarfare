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

final class StrategicBomber extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Strategic Bomber',
            nameMulti: 'Strategic Bombers',
            rowName: 'strategic_bomber',
            image: 'gu_strategic_bomber.jpg',
            netWorth: 250,
            timestamp: 72000,
            description: '',
            gameUnitCategory: GameUnitCategory::AIR_UNITS,
            behaviorClass: AirUnitBehavior::class,
            cost: new Cost(cash: 135000, food: 5000, wood: 1000, steel: 2500),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 1200, food: 200, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 3000,
                armor: 10,
                travelSpeed: 500,
                airBattleStats: new AirBattleStats(attack: 75, attackSpeed: 300, defence: 80, defenceSpeed: 310),
                groundBattleStats: new GroundBattleStats(attack: 20, attackSpeed: 100),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::STRATEGIC_BOMBER;
    }
}
