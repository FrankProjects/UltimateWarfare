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
            description: 'Strategic bombers are heavy and slow, and barely defend themselves in the air. '
                . 'Escort them with fighters to protect them from enemy aircraft, and beware of flak, '
                . 'which ignores their armor.',
            gameUnitCategory: GameUnitCategory::AIR_UNITS,
            behaviorClass: AirUnitBehavior::class,
            cost: new Cost(cash: 120000, food: 5000, wood: 1000, steel: 2500),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 500, food: 100, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 1200,
                armor: 5,
                travelSpeed: 500,
                airBattleStats: new AirBattleStats(attack: 200, attackSpeed: 100, defence: 250, defenceSpeed: 100),
                groundBattleStats: new GroundBattleStats(attack: 12000, attackSpeed: 50),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::STRATEGIC_BOMBER;
    }
}
