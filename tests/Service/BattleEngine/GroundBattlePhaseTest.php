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

class GroundBattlePhaseTest extends TestCase
{
    public function testMinesDetonateOnTroopsWithArmorPiercingPower(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 100);
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 5);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 2500);

        $battlePhase = $this->startGroundPhase([$attackingTanks, $attackingFighters], [$mineField]);

        // 10000 power ignores tank armor: 145 damage per tank. Fighters are not troops and are unharmed.
        self::assertSame(32, $attackingTanks->getAmount());
        self::assertSame(5, $attackingFighters->getAmount());
        self::assertSame(
            [
                'Starting ground Battle Phase',
                'Defending Mine Field (level 10) detonates with 10000 armor-piercing power',
                '68 attacking Tanks died in the fight',
                'Defending Mine Field (level 10) has 1250 mine health left',
                'Attacking Tanks fire with 12160 attack power',
                'defending Mine Field (level 10) was destroyed',
                'Attacking Fighters fire with 200 attack power',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testDamagedMineFieldDetonatesWeakerAndIsSpent(): void
    {
        $attackingSoldiers = $this->createFleetUnit(GameUnitEnum::SOLDIER, 200);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 1250);

        $battlePhase = $this->startGroundPhase([$attackingSoldiers], [$mineField]);

        // Half health detonates with 5000 power and spends the remaining mines
        self::assertSame(75, $attackingSoldiers->getAmount());
        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertContains('Defending Mine Field (level 10) has no mines left', $battlePhase->getBattleLog());
    }

    public function testMineSweepersClearMinesBeforeDetonation(): void
    {
        $attackingSweepers = $this->createFleetUnit(GameUnitEnum::MINE_SWEEPER, 80);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 2500);

        $battlePhase = $this->startGroundPhase([$attackingSweepers], [$mineField]);

        // 2000 mines cleared leaves 20% of the field: 2000 power kills 37 sweepers of 53 health
        self::assertSame(43, $attackingSweepers->getAmount());
        self::assertContains('Attacking Mine Sweepers clear 2000 mines', $battlePhase->getBattleLog());
        self::assertContains(
            'Defending Mine Field (level 10) detonates with 2000 armor-piercing power',
            $battlePhase->getBattleLog()
        );
    }

    public function testEnoughMineSweepersClearTheMineField(): void
    {
        $attackingSweepers = $this->createFleetUnit(GameUnitEnum::MINE_SWEEPER, 100);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 2500);

        $battlePhase = $this->startGroundPhase([$attackingSweepers], [$mineField]);

        self::assertSame(100, $attackingSweepers->getAmount());
        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertSame(
            [
                'Starting ground Battle Phase',
                'Attacking Mine Sweepers clear 2500 mines',
                'Defending Mine Field (level 10) was cleared',
                'Attacking Mine Sweepers fire with 1500 attack power',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testMineFieldDoesNotFireInSpeedTiersButCountsAsDefense(): void
    {
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 2500);

        $battlePhase = $this->startGroundPhase([], [$mineField]);

        self::assertSame(10000, $battlePhase->getDefensePower());
        self::assertSame(2500, $mineField->getHealth());
        self::assertSame(
            ['Starting ground Battle Phase', 'No resistance in this battle phase...'],
            $battlePhase->getBattleLog()
        );
    }

    public function testBunkerHalvesDamageToShelteredTroops(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 2);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 50);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 1500);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$defendingSoldiers, $bunker]);

        // 760 attack power is halved for the soldiers: 9 die instead of 19
        self::assertSame(41, $defendingSoldiers->getAmount());
        self::assertContains('Defending Bunker (level 1) shelters 50 of 50 troops', $battlePhase->getBattleLog());
    }

    public function testBunkerOnlySheltersTroopsUpToItsCapacity(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 4);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 200);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 1500);

        $this->startGroundPhase([$attackingTanks], [$defendingSoldiers, $bunker]);

        // Half of the soldiers are sheltered, so they take 75% of 1520 attack power
        self::assertSame(172, $defendingSoldiers->getAmount());
    }

    public function testDamagedBunkerSheltersFewerTroops(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 4);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 200);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 2, 1500);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$defendingSoldiers, $bunker]);

        // A level 2 bunker at half health has the capacity of a level 1 bunker
        self::assertSame(172, $defendingSoldiers->getAmount());
        self::assertContains('Defending Bunker (level 2) shelters 100 of 200 troops', $battlePhase->getBattleLog());
    }

    public function testArtilleryBombardsBunkerIgnoringArmor(): void
    {
        $attackingArtillery = $this->createFleetUnit(GameUnitEnum::ARTILLERY, 2);
        $attackingTank = $this->createFleetUnit(GameUnitEnum::TANK, 1);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 100);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 1500);

        $battlePhase = $this->startGroundPhase([$attackingArtillery, $attackingTank], [$defendingSoldiers, $bunker]);

        // The barrage takes 600 of the bunker's 1500 health before the fight, so it shelters only 60 of
        // 100 soldiers. The tank's 380 attack power then takes 70% effect on the soldiers and 380 / 10
        // armor on the bunker.
        self::assertSame(94, $defendingSoldiers->getAmount());
        self::assertSame(862, $bunker->getHealth());
        self::assertContains(
            'Attacking Artillery bombard the defending Bunker (level 1) for 600 damage',
            $battlePhase->getBattleLog()
        );
        self::assertContains('Defending Bunker (level 1) shelters 60 of 100 troops', $battlePhase->getBattleLog());
    }

    public function testArtilleryCanDestroyBunker(): void
    {
        $attackingArtillery = $this->createFleetUnit(GameUnitEnum::ARTILLERY, 5);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 100);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 1500);

        $battlePhase = $this->startGroundPhase([$attackingArtillery], [$defendingSoldiers, $bunker]);

        self::assertSame([$defendingSoldiers], array_values($battlePhase->getDefenderGameUnits()));
        self::assertContains('Defending Bunker (level 1) was destroyed', $battlePhase->getBattleLog());
    }

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    private function startGroundPhase(array $attackerGameUnits, array $defenderGameUnits): BattlePhase
    {
        $battlePhase = BattlePhase::factory(
            BattlePhase::GROUND_PHASE,
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
