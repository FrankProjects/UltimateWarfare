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

final class MineField extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Mine Field',
            nameMulti: 'Mine Fields',
            rowName: 'mine_field',
            image: 'gu_mine_field.jpg',
            netWorth: 0,
            timestamp: 3600,
            description: 'Mine fields detonate on attacking troops before the battle starts, '
                . 'piercing their armor. Detonated mines are spent and each detonation costs half of '
                . 'the mine field, so repair it to re-arm. Mine Sweepers clear mines before they explode. '
                . 'Each level lays an additional layer of mines.',
            gameUnitCategory: GameUnitCategory::DEFENSE_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 5500, food: 0, wood: 0, steel: 1),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 250,
                armor: 1,
                groundBattleStats: new GroundBattleStats(defence: 1000),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::MINE_FIELD;
    }
}
