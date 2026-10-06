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
use FrankProjects\UltimateWarfare\Entity\Research\RadarTechnologyResearch;

final class RadarStation extends GameUnit
{
    public function __construct()
    {
        parent::__construct(
            name: 'Radar Station',
            nameMulti: 'Radar Stations',
            rowName: 'radar_station',
            image: 'gu_radar_station.jpg',
            netWorth: 10,
            timestamp: 36000,
            description: 'Radar stations can detect enemy troop movements. Each upgrade level '
                . 'extends the radar vision range by one extra region.',
            gameUnitCategory: GameUnitCategory::SPECIAL_BUILDINGS,
            behaviorClass: null,
            cost: new Cost(cash: 50000, food: 0, wood: 2500, steel: 5000),
            income: new Income(cash: 0, food: 0, wood: 0, steel: 0),
            upkeep: new Upkeep(cash: 0, food: 0, wood: 0, steel: 0),
            battleStats: new BattleStats(health: 6000, armor: 2),
            researchClass: RadarTechnologyResearch::class,
        );
    }

    public function getGameUnitEnum(): GameUnitEnum
    {
        return GameUnitEnum::RADAR_STATION;
    }
}
