<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleLossCalculator;
use PHPUnit\Framework\TestCase;

class BattleLossCalculatorTest extends TestCase
{
    public function testPartialAndFullLossesOfALostBattle(): void
    {
        $attackingTanks = FleetUnit::createForFleet(new Fleet(), GameUnitEnum::TANK, 10);
        $attackingSoldiers = FleetUnit::createForFleet(new Fleet(), GameUnitEnum::SOLDIER, 50);
        $defendingSoldiers = WorldRegionStackableUnit::create(new WorldRegion(), GameUnitEnum::SOLDIER, 100);
        $bunker = WorldRegionLeveledUnit::create(new WorldRegion(), GameUnitEnum::BUNKER, 3, 12000);
        $barrack = WorldRegionLeveledUnit::create(new WorldRegion(), GameUnitEnum::BARRACK, 2, 12000);

        $calculator = new BattleLossCalculator(
            [$attackingTanks, $attackingSoldiers],
            [$defendingSoldiers, $bunker, $barrack]
        );

        // The phases change amounts in place and drop groups that died completely
        $attackingTanks->setAmount(7);
        $defendingSoldiers->setAmount(60);
        $bunker->setHealth(500);

        $lostUnits = $calculator->calculate([$attackingTanks], [$defendingSoldiers, $bunker], false);

        self::assertSame(
            [
                'attacker' => [GameUnitEnum::TANK->value => 3, GameUnitEnum::SOLDIER->value => 50],
                'defender' => [GameUnitEnum::SOLDIER->value => 40],
                'defenderBuildings' => [GameUnitEnum::BARRACK->value => 2],
            ],
            $lostUnits
        );
    }

    public function testCapturedRegionLosesAllDefendersButSurvivingReinforcementsReturnHome(): void
    {
        $attackingTanks = FleetUnit::createForFleet(new Fleet(), GameUnitEnum::TANK, 10);
        $house = WorldRegionStackableUnit::create(new WorldRegion(), GameUnitEnum::HOUSE, 20);
        $barrack = WorldRegionLeveledUnit::create(new WorldRegion(), GameUnitEnum::BARRACK, 2, 12000);
        $reinforcingSoldiers = new WorldRegionStackableUnit();
        $reinforcingSoldiers->setGameUnit(GameUnitEnum::SOLDIER);
        $reinforcingSoldiers->setAmount(25);
        $reinforcingFighters = new WorldRegionStackableUnit();
        $reinforcingFighters->setGameUnit(GameUnitEnum::FIGHTER);
        $reinforcingFighters->setAmount(10);

        $calculator = new BattleLossCalculator(
            [$attackingTanks],
            [$house, $barrack, $reinforcingSoldiers, $reinforcingFighters],
            [$reinforcingSoldiers, $reinforcingFighters]
        );

        // The houses and barrack survived the fighting, but are lost with the region. Fighters survive, soldiers die.
        $lostUnits = $calculator->calculate([$attackingTanks], [$house, $barrack, $reinforcingFighters], true);

        self::assertSame(
            [
                'attacker' => [],
                'defender' => [GameUnitEnum::HOUSE->value => 20, GameUnitEnum::SOLDIER->value => 25],
                'defenderBuildings' => [GameUnitEnum::BARRACK->value => 2],
            ],
            $lostUnits
        );
    }

    public function testLossesOfTheSameUnitAreAddedUp(): void
    {
        $defendingSoldiers = WorldRegionStackableUnit::create(new WorldRegion(), GameUnitEnum::SOLDIER, 100);
        $reinforcingSoldiers = new WorldRegionStackableUnit();
        $reinforcingSoldiers->setGameUnit(GameUnitEnum::SOLDIER);
        $reinforcingSoldiers->setAmount(25);

        $calculator = new BattleLossCalculator(
            [],
            [$defendingSoldiers, $reinforcingSoldiers],
            [$reinforcingSoldiers]
        );

        $defendingSoldiers->setAmount(90);
        $reinforcingSoldiers->setAmount(20);

        $lostUnits = $calculator->calculate([], [$defendingSoldiers, $reinforcingSoldiers], false);

        self::assertSame([GameUnitEnum::SOLDIER->value => 15], $lostUnits['defender']);
    }
}
