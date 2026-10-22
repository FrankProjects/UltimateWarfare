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
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 4, 6000);

        $battlePhase = $this->startAirPhase(
            [$attackingBombers, $attackingFighters, $attackingDestroyers],
            [$antiAircraftGun]
        );

        // 4800 flak kills 9 escorted bombers of 520 health; 2400 flak downs 3 fighters (625) through their armor
        self::assertSame(1, $attackingBombers->getAmount());
        self::assertSame(7, $attackingFighters->getAmount());
        self::assertSame(2, $attackingDestroyers->getAmount());
        self::assertSame(
            [
                'Starting air Battle Phase',
                'Defending Anti Aircraft Gun (level 4) fires flak with 4800 power',
                '9 attacking Bombers died in the fight',
                '3 attacking Fighters died in the fight',
                'Attacking Fighters fire with 8400 attack power',
                'defending Anti Aircraft Gun (level 4) took 4200 damage, 1800 health remaining',
                'Attacking Bombers fire with 100 attack power',
                'defending Anti Aircraft Gun (level 4) took 50 damage, 1750 health remaining',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testAntiAircraftGunDoesNotFireInSpeedTiersButCountsAsDefense(): void
    {
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 10, 15000);

        $battlePhase = $this->startAirPhase([], [$antiAircraftGun]);

        self::assertSame(12000, $battlePhase->getDefensePower());
        self::assertSame(
            ['Starting air Battle Phase', 'No resistance in this battle phase...'],
            $battlePhase->getBattleLog()
        );
    }

    public function testFullyEscortedBombersTakeQuarterDamage(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 10);
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 13);

        $battlePhase = $this->startAirPhase([$attackingFighters, $attackingBombers], [$defendingFighters]);

        // 16900 defense power: the escorts are all shot down, but still protect the bombers in that volley.
        // The bombers take 4225, which kills 2 of 2080 health.
        self::assertSame(8, $attackingBombers->getAmount());
        self::assertNotContains($attackingFighters, $battlePhase->getAttackerGameUnits());
        self::assertContains('Attacking Fighters escort 10 of 10 bombers', $battlePhase->getBattleLog());
    }

    public function testPartiallyEscortedBombersTakeReducedDamage(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 5);
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 13);

        $this->startAirPhase([$attackingFighters, $attackingBombers], [$defendingFighters]);

        // Half of the bombers are escorted, so they take 62.5% of 16900: 5 die
        self::assertSame(5, $attackingBombers->getAmount());
    }

    public function testDefendingFightersEscortDefendingBombers(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 29);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 10);
        $defendingBombers = $this->createStackableUnit(GameUnitEnum::BOMBER, 10);

        $battlePhase = $this->startAirPhase([$attackingFighters], [$defendingFighters, $defendingBombers]);

        // 20 attackers are shot down first; 10800 attack power reaches the escorted bombers as 2700: 1 dies
        self::assertSame(9, $defendingBombers->getAmount());
        self::assertContains('Defending Fighters escort 10 of 10 bombers', $battlePhase->getBattleLog());
    }

    public function testRadarStrengthensFlak(): void
    {
        $attackingBombers = $this->createFleetUnit(GameUnitEnum::BOMBER, 10);
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 2, 3000);
        $radar = $this->createLeveledUnit(GameUnitEnum::RADAR_STATION, 5, 30000);

        $battlePhase = $this->startAirPhase([$attackingBombers], [$antiAircraftGun, $radar]);

        // 3600 flak kills 6 bombers of 520 health
        self::assertSame(4, $attackingBombers->getAmount());
        self::assertContains(
            'Defending Radar Station (level 5) guides the air defence: +50% defense power',
            $battlePhase->getBattleLog()
        );
        self::assertContains(
            'Defending Anti Aircraft Gun (level 2) fires flak with 3600 power',
            $battlePhase->getBattleLog()
        );
    }

    public function testRadarStrengthensDefendingFighters(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 10);
        $defendingFighters = $this->createStackableUnit(GameUnitEnum::FIGHTER, 1);
        $radar = $this->createLeveledUnit(GameUnitEnum::RADAR_STATION, 5, 30000);

        $this->startAirPhase([$attackingFighters], [$defendingFighters, $radar]);

        // 1300 defense power becomes 1950, killing 3 fighters of 625 instead of 2
        self::assertSame(7, $attackingFighters->getAmount());
    }

    public function testDamagedRadarGivesSmallerBonus(): void
    {
        $antiAircraftGun = $this->createLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN, 10, 15000);
        $radar = $this->createLeveledUnit(GameUnitEnum::RADAR_STATION, 5, 15000);

        $battlePhase = $this->startAirPhase([], [$antiAircraftGun, $radar]);

        self::assertSame(15000, $battlePhase->getDefensePower());
    }

    public function testAircraftDoNotStrikeGroundTroops(): void
    {
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 100);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 100);

        $battlePhase = $this->startAirPhase([$attackingFighters], [$defendingSoldiers]);

        // Soldiers do not fight in the air, so the fighters' air attack does not reach them
        self::assertSame(100, $defendingSoldiers->getAmount());
        self::assertSame([$defendingSoldiers], array_values($battlePhase->getDefenderGameUnits()));
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
