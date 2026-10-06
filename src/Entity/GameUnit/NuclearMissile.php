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

final class NuclearMissile extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Nuclear Missile',
            nameMulti: 'Nuclear Missiles',
            rowName: 'nuclear_missile',
            image: 'gu_nuclear_missile.jpg',
            netWorth: 10,
            timestamp: 36000,
            description: '',
            gameUnitCategory: GameUnitCategory::MISSILES,
            behaviorClass: null,
            cost: new Cost(cash: 1000000, food: 0, wood: 0, steel: 0),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::NUCLEAR_MISSILE;
    }
}
