<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;
use PHPUnit\Framework\TestCase;

class BattlePhaseTest extends TestCase
{
    public function testFasterAttackerDestroysDefendersBeforeTheyFire(): void
    {
        // Tanks attack at speed 130, defending soldiers fire at speed 110
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 10);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 10);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$defendingSoldiers]);

        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertSame(0, $battlePhase->getDefensePower());
        self::assertSame(10, $attackingTanks->getAmount());
        self::assertNotContains(
            'Defending Soldiers fire with 120 defense power',
            $battlePhase->getBattleLog()
        );
    }

    public function testFasterDefenderFiresBeforeSlowerAttacker(): void
    {
        // Defending soldiers fire at speed 110 and kill 2 attackers before they fire at speed 100
        $attackingSoldiers = $this->createFleetUnit(GameUnitEnum::SOLDIER, 100);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 10);

        $battlePhase = $this->startGroundPhase([$attackingSoldiers], [$defendingSoldiers]);

        self::assertSame(98, $attackingSoldiers->getAmount());
        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertSame(
            [
                'Starting ground Battle Phase',
                'Defending Soldiers fire with 120 defense power',
                '2 attacking Soldiers died in the fight',
                'Attacking Soldiers fire with 980 attack power',
                'All defending Soldiers died in the fight',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testUnitsWithEqualSpeedFireSimultaneously(): void
    {
        // Soldiers attack and mine sweepers defend both at speed 100
        $attackingSoldiers = $this->createFleetUnit(GameUnitEnum::SOLDIER, 20);
        $defendingMineSweepers = $this->createStackableUnit(GameUnitEnum::MINE_SWEEPER, 30);

        $this->startGroundPhase([$attackingSoldiers], [$defendingMineSweepers]);

        // 300 defense power kills 6 soldiers (50 health), the full 200 attack power still kills 1 sweeper
        self::assertSame(14, $attackingSoldiers->getAmount());
        self::assertSame(29, $defendingMineSweepers->getAmount());
    }

    public function testPowerBelowOneKillCarriesOverToNextSpeedTier(): void
    {
        // 55 power at speed 135 and 48 power at speed 110 add up to 2 kills of 50 health soldiers
        $attackingSoldiers = $this->createFleetUnit(GameUnitEnum::SOLDIER, 10);
        $defendingTank = $this->createStackableUnit(GameUnitEnum::TANK, 1);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 4);

        $this->startGroundPhase([$attackingSoldiers], [$defendingTank, $defendingSoldiers]);

        self::assertSame(8, $attackingSoldiers->getAmount());
    }

    public function testDefenderDamageMultiplierReducesAttackPower(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 10);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 10);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$defendingSoldiers], 0.5);

        self::assertSame(5, $defendingSoldiers->getAmount());
        self::assertContains(
            "Defender's defensive network reduces incoming damage from 500 to 250",
            $battlePhase->getBattleLog()
        );
    }

    public function testLeveledBuildingWithoutPowerStillTakesDamage(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 10);
        $barrack = WorldRegionLeveledUnit::create(new WorldRegion(), GameUnitEnum::BARRACK, 1, 6000);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$barrack]);

        // 500 attack power against armor 2
        self::assertSame(5750, $barrack->getHealth());
        self::assertSame(0, $battlePhase->getDefensePower());
    }

    public function testPhaseWithoutPowerHasNoResistance(): void
    {
        $battlePhase = $this->startGroundPhase([], []);

        self::assertSame(
            ['Starting ground Battle Phase', 'No resistance in this battle phase...'],
            $battlePhase->getBattleLog()
        );
    }

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    private function startGroundPhase(
        array $attackerGameUnits,
        array $defenderGameUnits,
        float $defenderDamageMultiplier = 1.0
    ): BattlePhase {
        $battlePhase = BattlePhase::factory(
            BattlePhase::GROUND_PHASE,
            $attackerGameUnits,
            $defenderGameUnits,
            new GameUnitRegistry(),
            $defenderDamageMultiplier
        );
        $battlePhase->startBattlePhase();

        return $battlePhase;
    }

    private function createFleetUnit(GameUnitEnum $gameUnit, int $amount): FleetUnit
    {
        return FleetUnit::createForFleet(new Fleet(), $gameUnit, $amount);
    }

    private function createStackableUnit(GameUnitEnum $gameUnit, int $amount): WorldRegionStackableUnit
    {
        return WorldRegionStackableUnit::create(new WorldRegion(), $gameUnit, $amount);
    }
}
