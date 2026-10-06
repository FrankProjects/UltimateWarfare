<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Entity;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use PHPUnit\Framework\TestCase;

class WorldRegionTest extends TestCase
{
    public function testCreateStackableUnitAddsItToRegion(): void
    {
        $region = new WorldRegion();
        $unit = WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);

        self::assertSame([$unit], $region->getWorldRegionStackableUnits()->toArray());
    }

    public function testCreateLeveledUnitAddsItToRegion(): void
    {
        $region = new WorldRegion();
        $unit = WorldRegionLeveledUnit::create($region, GameUnitEnum::BARRACK, 1, 6000);

        self::assertSame([$unit], $region->getWorldRegionLeveledUnits()->toArray());
        self::assertSame($unit, $region->getLeveledUnit(GameUnitEnum::BARRACK));
    }

    public function testRemoveStackableUnit(): void
    {
        $region = new WorldRegion();
        $soldiers = WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);
        $tanks = WorldRegionStackableUnit::create($region, GameUnitEnum::TANK, 5);

        $region->removeWorldRegionStackableUnit($soldiers);

        self::assertSame([$tanks], array_values($region->getWorldRegionStackableUnits()->toArray()));
    }

    public function testRemoveLeveledUnit(): void
    {
        $region = new WorldRegion();
        $barrack = WorldRegionLeveledUnit::create($region, GameUnitEnum::BARRACK, 1, 6000);

        $region->removeWorldRegionLeveledUnit($barrack);

        self::assertTrue($region->getWorldRegionLeveledUnits()->isEmpty());
        self::assertNull($region->getLeveledUnit(GameUnitEnum::BARRACK));
    }

    public function testCreateConstructionAddsItToRegion(): void
    {
        $region = new WorldRegion();
        $construction = Construction::create($region, new Player(), GameUnitEnum::FARM, 5, 3600);

        self::assertSame([$construction], $region->getConstructions()->toArray());

        $region->removeConstruction($construction);

        self::assertTrue($region->getConstructions()->isEmpty());
    }
}
