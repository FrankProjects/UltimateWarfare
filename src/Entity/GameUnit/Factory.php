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
use FrankProjects\UltimateWarfare\Entity\Research\FactoryBlueprintResearch;

final class Factory extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Factory',
            nameMulti: 'Factories',
            rowName: 'factory',
            image: 'gu_factory.jpg',
            netWorth: 10,
            timestamp: 21600,
            description: 'Factory can build armor and mechanized vehicles. Each upgrade level '
                . 'builds tanks and artillery in this region 5% faster (up to 45% at level 10).',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 10000, food: 0, wood: 500, steel: 750),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 9000, armor: 2),
            researchClass: FactoryBlueprintResearch::class,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::FACTORY;
    }
}
