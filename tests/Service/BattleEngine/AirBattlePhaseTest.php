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

class AirBattlePhaseTest extends TestCase
{
    public function testFlakIgnoresBomberArmorAndEscortsAndHalfHurtsFighters(): void
    {
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 10);
        $attackingDestroyers = $this->createFleetUnit(GameUnitEnum::DESTROYER, 2);
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 10, 15000);

        $battlePhase = $this->startAirPhase(
            [$attackingBombers, $attackingFighters, $attackingDestroyers],
            [$antiAircraftGun]
        );

        // 4000 flak kills 3 escorted bombers of 1250 health; 2000 flak can't down a fighter (5000)
        self::assertSame(7, $attackingBombers->getAmount());
        self::assertSame(10, $attackingFighters->getAmount());
        self::assertSame(2, $attackingDestroyers->getAmount());
        self::assertSame(
            [
                'Starting air Battle Phase',
                'Defending Anti Aircraft Gun (level 10) fires flak with 4000 power',
                '3 attacking Bombers died in the fight',
                'Attacking Fighters fire with 750 attack power',
                'defending Anti Aircraft Gun (level 10) took 375 damage, 14625 health remaining',
                'Attacking Bombers fire with 105 attack power',
                'defending Anti Aircraft Gun (level 10) took 52 damage, 14573 health remaining',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testAntiAircraftGunDoesNotFireInSpeedTiersButCountsAsDefense(): void
    {
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 10, 15000);

        $battlePhase = $this->startAirPhase([], [$antiAircraftGun]);

        self::assertSame(4000, $battlePhase->getDefensePower());
        self::assertSame(
            ['Starting air Battle Phase', 'No resistance in this battle phase...'],
            $battlePhase->getBattleLog()
        );
    }

    public function testFullyEscortedBombersTakeQuarterDamage(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 10);
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 2500);

        $battlePhase = $this->startAirPhase([$attackingFighters, $attackingBombers], [$defendingFighters]);

        // 200000 defense power: the escorts are all shot down, but still protect the bombers in that volley.
        // The bombers take 50000, which kills 2 of 25000 health.
        self::assertSame(8, $attackingBombers->getAmount());
        self::assertNotContains($attackingFighters, $battlePhase->getAttackerGameUnits());
        self::assertContains('Attacking Fighters escort 10 of 10 bombers', $battlePhase->getBattleLog());
    }

    public function testPartiallyEscortedBombersTakeReducedDamage(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 5);
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 2500);

        $this->startAirPhase([$attackingFighters, $attackingBombers], [$defendingFighters]);

        // Half of the bombers are escorted, so they take 62.5% of 200000: 5 die
        self::assertSame(5, $attackingBombers->getAmount());
    }

    public function testDefendingFightersEscortDefendingBombers(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 2500);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 10);
        $defendingBombers = $this->createStackableUnit(GameUnitEnum::BOMBER, 10);

        $battlePhase = $this->startAirPhase([$attackingFighters], [$defendingFighters, $defendingBombers]);

        // 187500 attack power reaches the escorted bombers as 46875: 1 dies
        self::assertSame(9, $defendingBombers->getAmount());
        self::assertContains('Defending Fighters escort 10 of 10 bombers', $battlePhase->getBattleLog());
    }

    public function testRadarStrengthensFlak(): void
    {
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 10, 15000);
        $radar = $this->createLeveledUnit(GameUnitEnum::RADAR_STATION, 5, 30000);

        $battlePhase = $this->startAirPhase([$attackingBombers], [$antiAircraftGun, $radar]);

        // 6000 flak kills 4 bombers of 1250 health
        self::assertSame(6, $attackingBombers->getAmount());
        self::assertContains(
            'Defending Radar Station (level 5) guides the air defence: +50% defense power',
            $battlePhase->getBattleLog()
        );
        self::assertContains(
            'Defending Anti Aircraft Gun (level 10) fires flak with 6000 power',
            $battlePhase->getBattleLog()
        );
    }

    public function testRadarStrengthensDefendingFighters(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 10);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 100);
        $radar = $this->createLeveledUnit(GameUnitEnum::RADAR_STATION, 5, 30000);

        $this->startAirPhase([$attackingFighters], [$defendingFighters, $radar]);

        // 8000 defense power becomes 12000, killing 2 fighters of 5000 instead of 1
        self::assertSame(8, $attackingFighters->getAmount());
    }

    public function testDamagedRadarGivesSmallerBonus(): void
    {
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 10, 15000);
        $radar = $this->createLeveledUnit(GameUnitEnum::RADAR_STATION, 5, 15000);

        $battlePhase = $this->startAirPhase([], [$antiAircraftGun, $radar]);

        self::assertSame(5000, $battlePhase->getDefensePower());
    }

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    private function startAirPhase(array $attackerGameUnits, array $defenderGameUnits): BattlePhase
    {
        $battlePhase = BattlePhase::factory(
            BattlePhase::AIR_PHASE,
            $attackerGameUnits,
            $defenderGameUnits,
            new GameUnitRegistry()
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

    private function createLeveledUnit(GameUnitEnum $gameUnit, int $level, int $health): WorldRegionLeveledUnit
    {
        return WorldRegionLeveledUnit::create(new WorldRegion(), $gameUnit, $level, $health);
    }
}
