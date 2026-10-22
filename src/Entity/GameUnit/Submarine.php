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
use FrankProjects\UltimateWarfare\Entity\BattleStats\SeaBattleStats;
use FrankProjects\UltimateWarfare\Entity\Research\SubmarineTechnologyResearch;
use FrankProjects\UltimateWarfare\Service\GameUnit\Behavior\NavalUnitBehavior;

final class Submarine extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Submarine',
            nameMulti: 'Submarines',
            rowName: 'submarine',
            image: 'gu_submarine.jpg',
            netWorth: 500,
            timestamp: 43200,
            description: '',
            gameUnitCategory: GameUnitCategory::NAVAL_UNITS,
            behaviorClass: NavalUnitBehavior::class,
            cost: new Cost(cash: 70000, food: 8000, wood: 800, steel: 2000),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 600, food: 150, wood: 0, steel: 0),
            battleStats: new BattleStats(
                health: 500,
                armor: 8,
                travelSpeed: 100,
                seaBattleStats: new SeaBattleStats(attack: 15000, attackSpeed: 200, defence: 8000, defenceSpeed: 200),
            ),
            researchClass: SubmarineTechnologyResearch::class,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::SUBMARINE;
    }
}
