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
            description: 'Mine fields damage ground forces attacking this region. '
                . 'Each level lays an additional layer of mines, increasing both '
                . 'their ground defence and how much punishment they can absorb.',
            gameUnitCategory: GameUnitCategory::DEFENSE_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 5500, food: 0, wood: 0, steel: 1),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 250,
                armor: 1,
                groundBattleStats: new GroundBattleStats(defence: 5, defenceSpeed: 500),
            ),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::MINE_FIELD;
    }
}
