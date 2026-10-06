<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Repository;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use PHPUnit\Framework\TestCase;

class GameUnitRegistryTest extends TestCase
{
    public function testRegionUnitSummaryIncludesConstructions(): void
    {
        $region = new WorldRegion();
        $player = new Player();

        WorldRegionStackableUnit::create($region, GameUnitEnum::FARM, 100);
        Construction::create($region, $player, GameUnitEnum::FARM, 20, 3600);
        Construction::create($region, $player, GameUnitEnum::FARM, 5, 3600);
        Construction::create($region, $player, GameUnitEnum::WOODCUTTER, 50, 3600);

        WorldRegionLeveledUnit::create($region, GameUnitEnum::BARRACK, 2, 12000);
        Construction::create($region, $player, GameUnitEnum::BARRACK, 1, 3600);
        Construction::create($region, $player, GameUnitEnum::BARRACK, 1, 3600);
        Construction::create($region, $player, GameUnitEnum::AIRFIELD, 1, 3600);

        $summary = (new GameUnitRegistry())->getRegionUnitSummary($region);

        self::assertSame(100, $summary['buildings']);
        self::assertSame(1, $summary['special']);
        self::assertSame(0, $summary['troops']);
        self::assertSame(
            [
                'buildings' => 75,
                'defences' => 0,
                'special' => 1,
                'troops' => 0,
                'navalUnits' => 0,
                'airUnits' => 0,
                'missiles' => 0,
            ],
            $summary['inConstruction']
        );

        self::assertIsArray($summary['details']);
        self::assertSame(
            [
                ['name' => 'Farm', 'amount' => 100, 'inConstruction' => 25],
                ['name' => 'Woodcutter', 'amount' => 0, 'inConstruction' => 50],
            ],
            $summary['details']['buildings']
        );
        self::assertSame(
            [
                ['name' => 'Barrack', 'amount' => 1, 'inConstruction' => 2, 'level' => 2],
                ['name' => 'Airfield', 'amount' => 0, 'inConstruction' => 1, 'level' => 0],
            ],
            $summary['details']['special']
        );
    }

    public function testRegionUnitSummaryWithoutConstructions(): void
    {
        $region = new WorldRegion();
        WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);

        $summary = (new GameUnitRegistry())->getRegionUnitSummary($region);

        self::assertSame(10, $summary['troops']);
        self::assertIsArray($summary['inConstruction']);
        self::assertSame(0, array_sum($summary['inConstruction']));
        self::assertIsArray($summary['details']);
        self::assertSame([['name' => 'Soldier', 'amount' => 10, 'inConstruction' => 0]], $summary['details']['troops']);
    }
}
