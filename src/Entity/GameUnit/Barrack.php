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

final class Barrack extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Barrack',
            nameMulti: 'Baracks',
            rowName: 'barrack',
            image: 'gu_barrack.jpg',
            netWorth: 10,
            timestamp: 3600,
            description: 'Within a barrack you can train troops. Each upgrade level trains '
                . 'troops in this region 5% faster (up to 45% at level 10).',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 2000, food: 0, wood: 100, steel: 250),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 6000, armor: 2),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::BARRACK;
    }
}
