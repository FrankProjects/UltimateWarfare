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
use FrankProjects\UltimateWarfare\Entity\Research\BallisticMissileTechnologyResearch;

final class MissileFactory extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Missile Factory',
            nameMulti: 'Missile Factories',
            rowName: 'missile_factory',
            image: 'gu_missile_factory.jpg',
            netWorth: 10,
            timestamp: 72000,
            description: 'Used to build and launch missiles. Each upgrade level builds '
                . 'missiles in this region 5% faster (up to 45% at level 10).',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 50000, food: 0, wood: 2500, steel: 5000),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 10000, armor: 2),
            researchClass: BallisticMissileTechnologyResearch::class,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::MISSILE_FACTORY;
    }
}
