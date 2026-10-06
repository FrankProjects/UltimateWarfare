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

final class Bunker extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Bunker',
            nameMulti: 'Bunkers',
            rowName: 'bunker',
            image: 'gu_bunker.jpg',
            netWorth: 0,
            timestamp: 900,
            description: 'Bunkers fortify this region against ground assaults. Each level '
                . 'reinforces the bunker, increasing both its ground defence and durability.',
            gameUnitCategory: GameUnitCategory::DEFENSE_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 8500, food: 50, wood: 150, steel: 150),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 4000,
                armor: 35,
                groundBattleStats: new GroundBattleStats(defence: 1, defenceSpeed: 0),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::BUNKER;
    }
}
