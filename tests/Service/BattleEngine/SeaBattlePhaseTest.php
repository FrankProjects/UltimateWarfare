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

class SeaBattlePhaseTest extends TestCase
{
    public function testSeaMinesDetonateOnShipsWithArmorPiercingPower(): void
    {
        $attackingCruisers = $this->createFleetUnit(GameUnitEnum::CRUISER, 4);
        $attackingPatrolBoats = $this->createFleetUnit(GameUnitEnum::PATROL_BOAT, 10);
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 5);
        $seaMines = $this->createLeveledUnit(GameUnitEnum::SEA_MINE, 10, 250);

        $battlePhase = $this->startSeaPhase(
            [$attackingCruisers, $attackingPatrolBoats, $attackingFighters],
            [$seaMines]
        );

        // 7500 power ignores ship armor: 2100 damage per cruiser and 245 per patrol boat.
        // Fighters are not naval units and are unharmed.
        self::assertSame(1, $attackingCruisers->getAmount());
        self::assertSame(5, $attackingFighters->getAmount());
        self::assertSame(
            [
                'Starting sea Battle Phase',
                'Defending Sea Mine (level 10) detonates with 7500 armor-piercing power',
                '3 attacking Cruisers died in the fight',
                'All attacking Patrol boats died in the fight',
                'Defending Sea Mine (level 10) has 125 mine health left',
                'Attacking Cruisers fire with 12000 attack power',
                'defending Sea Mine (level 10) was destroyed',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testMineCountermeasuresShipsWeakenSeaMines(): void
    {
        $attackingCountermeasuresShips = $this->createFleetUnit(GameUnitEnum::MINE_COUNTERMEASURES_SHIP, 5);
        $attackingDestroyers = $this->createFleetUnit(GameUnitEnum::DESTROYER, 6);
        $seaMines = $this->createLeveledUnit(GameUnitEnum::SEA_MINE, 10, 250);

        $battlePhase = $this->startSeaPhase([$attackingCountermeasuresShips, $attackingDestroyers], [$seaMines]);

        // 125 mines cleared halves the detonation, which then spends the remaining mines
        self::assertSame(2, $attackingDestroyers->getAmount());
        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertContains('Attacking Mine Countermeasures Ships clear 125 mines', $battlePhase->getBattleLog());
        self::assertContains(
            'Defending Sea Mine (level 10) detonates with 3750 armor-piercing power',
            $battlePhase->getBattleLog()
        );
        self::assertContains('Defending Sea Mine (level 10) has no mines left', $battlePhase->getBattleLog());
    }

    public function testEnoughMineCountermeasuresShipsClearTheSeaMines(): void
    {
        $attackingCountermeasuresShips = $this->createFleetUnit(GameUnitEnum::MINE_COUNTERMEASURES_SHIP, 10);
        $attackingCruisers = $this->createFleetUnit(GameUnitEnum::CRUISER, 1);
        $seaMines = $this->createLeveledUnit(GameUnitEnum::SEA_MINE, 10, 250);

        $battlePhase = $this->startSeaPhase([$attackingCountermeasuresShips, $attackingCruisers], [$seaMines]);

        self::assertSame(10, $attackingCountermeasuresShips->getAmount());
        self::assertSame(1, $attackingCruisers->getAmount());
        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertContains('Defending Sea Mine (level 10) was cleared', $battlePhase->getBattleLog());
    }

    public function testSeaMinesDoNotFireInSpeedTiersButCountAsDefense(): void
    {
        $seaMines = $this->createLeveledUnit(GameUnitEnum::SEA_MINE, 10, 250);

        $battlePhase = $this->startSeaPhase([], [$seaMines]);

        self::assertSame(7500, $battlePhase->getDefensePower());
        self::assertSame(250, $seaMines->getHealth());
        self::assertSame(
            ['Starting sea Battle Phase', 'No resistance in this battle phase...'],
            $battlePhase->getBattleLog()
        );
    }

    public function testShipsDoNotStrikeGroundTroops(): void
    {
        $attackingPatrolBoats = $this->createFleetUnit(GameUnitEnum::PATROL_BOAT, 100);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 100);

        $battlePhase = $this->startSeaPhase([$attackingPatrolBoats], [$defendingSoldiers]);

        // Soldiers do not fight at sea, so the patrol boats' sea attack does not reach them
        self::assertSame(100, $defendingSoldiers->getAmount());
        self::assertSame([$defendingSoldiers], array_values($battlePhase->getDefenderGameUnits()));
    }

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    private function startSeaPhase(array $attackerGameUnits, array $defenderGameUnits): BattlePhase
    {
        $battlePhase = BattlePhase::factory(
            BattlePhase::SEA_PHASE,
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
