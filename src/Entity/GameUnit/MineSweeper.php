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

final class MineSweeper extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Mine Sweeper',
            nameMulti: 'Mine Sweepers',
            rowName: 'minesweeper',
            image: 'gu_minesweeper.jpg',
            netWorth: 1,
            timestamp: 3600,
            description: 'This Soldier is trained to find and disarm mines.',
            gameUnitCategory: GameUnitCategory::TROOPS,
            behaviorClass: null,
            cost: new Cost(cash: 1500, food: 75, wood: 2, steel: 15),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 1, food: 1, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 150,
                armor: 1,
                travelSpeed: 200,
                groundBattleStats: new GroundBattleStats(
                    attack: 15,
                    attackSpeed: 100,
                    defence: 10,
                    defenceSpeed: 100,
                ),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::MINE_SWEEPER;
    }
}
