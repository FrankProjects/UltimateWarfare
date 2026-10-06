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
use FrankProjects\UltimateWarfare\Entity\Research\AdvancedOpticsResearch;

final class Sniper extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Sniper',
            nameMulti: 'Snipers',
            rowName: 'sniper',
            image: 'gu_sniper.jpg',
            netWorth: 1,
            timestamp: 7200,
            description: 'Snipers can be sent to enemy countries to take down enemy soldiers',
            gameUnitCategory: GameUnitCategory::TROOPS,
            behaviorClass: null,
            cost: new Cost(cash: 2500, food: 0, wood: 1, steel: 5),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 2, food: 2, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 0, armor: 0),
            researchClass: AdvancedOpticsResearch::class,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::SNIPER;
    }
}
