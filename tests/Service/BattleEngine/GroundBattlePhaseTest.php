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
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 10);
        $attackingFighters = $this->createFleetUnit(GameUnitEnum::FIGHTER, 5);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 2500);

        $battlePhase = $this->startGroundPhase([$attackingTanks, $attackingFighters], [$mineField]);

        // 3000 power ignores tank armor: 1500 damage per tank. Fighters are not troops and are unharmed.
        self::assertSame(8, $attackingTanks->getAmount());
        self::assertSame(5, $attackingFighters->getAmount());
        self::assertSame(
            [
                'Starting ground Battle Phase',
                'Defending Mine Field (level 10) detonates with 3000 armor-piercing power',
                '2 attacking Tanks died in the fight',
                'Defending Mine Field (level 10) has 1250 mine health left',
                'Attacking Tanks fire with 400 attack power',
                'defending Mine Field (level 10) took 400 damage, 850 health remaining',
                'Attacking Fighters fire with 100 attack power',
                'defending Mine Field (level 10) took 100 damage, 750 health remaining',
            ],
            $battlePhase->getBattleLog()
        );
    }

    public function testDamagedMineFieldDetonatesWeakerAndIsSpent(): void
    {
        $attackingSoldiers = $this->createFleetUnit(GameUnitEnum::SOLDIER, 100);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 1250);

        $battlePhase = $this->startGroundPhase([$attackingSoldiers], [$mineField]);

        // Half health detonates with 1500 power and spends the remaining mines
        self::assertSame(70, $attackingSoldiers->getAmount());
        self::assertSame([], $battlePhase->getDefenderGameUnits());
        self::assertContains('Defending Mine Field (level 10) has no mines left', $battlePhase->getBattleLog());
    }

    public function testMineSweepersClearMinesBeforeDetonation(): void
    {
        $attackingSweepers = $this->createFleetUnit(GameUnitEnum::MINE_SWEEPER, 40);
        $mineField = $this->createLeveledUnit(GameUnitEnum::MINE_FIELD, 10, 2500);

        $battlePhase = $this->startGroundPhase([$attackingSweepers], [$mineField]);

        // 1000 mines cleared leaves 60% of the field: 1800 power kills 12 sweepers of 150 health
        self::assertSame(28, $attackingSweepers->getAmount());
        self::assertContains('Attacking Mine Sweepers clear 1000 mines', $battlePhase->getBattleLog());
        self::assertContains(
            'Defending Mine Field (level 10) detonates with 1800 armor-piercing power',
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

        self::assertSame(3000, $battlePhase->getDefensePower());
        self::assertSame(2500, $mineField->getHealth());
        self::assertSame(
            ['Starting ground Battle Phase', 'No resistance in this battle phase...'],
            $battlePhase->getBattleLog()
        );
    }

    public function testBunkerHalvesDamageToShelteredTroops(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 10);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 50);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 4000);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$defendingSoldiers, $bunker]);

        // 500 attack power is halved for the soldiers: 5 die instead of 10
        self::assertSame(45, $defendingSoldiers->getAmount());
        self::assertContains('Defending Bunker (level 1) shelters 50 of 50 troops', $battlePhase->getBattleLog());
    }

    public function testBunkerOnlySheltersTroopsUpToItsCapacity(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 20);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 200);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 4000);

        $this->startGroundPhase([$attackingTanks], [$defendingSoldiers, $bunker]);

        // Half of the soldiers are sheltered, so they take 75% of 1000 attack power
        self::assertSame(185, $defendingSoldiers->getAmount());
    }

    public function testDamagedBunkerSheltersFewerTroops(): void
    {
        $attackingTanks = $this->createFleetUnit(GameUnitEnum::TANK, 20);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 200);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 2, 4000);

        $battlePhase = $this->startGroundPhase([$attackingTanks], [$defendingSoldiers, $bunker]);

        // A level 2 bunker at half health has the capacity of a level 1 bunker
        self::assertSame(185, $defendingSoldiers->getAmount());
        self::assertContains('Defending Bunker (level 2) shelters 100 of 200 troops', $battlePhase->getBattleLog());
    }

    public function testArtilleryBombardsBunkerIgnoringArmor(): void
    {
        $attackingArtillery = $this->createFleetUnit(GameUnitEnum::ARTILLERY, 40);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 100);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 4000);

        $battlePhase = $this->startGroundPhase([$attackingArtillery], [$defendingSoldiers, $bunker]);

        // The barrage halves the bunker before the fight, so it shelters only 50 of 100 soldiers.
        // 2000 attack power then takes 75% effect on the soldiers and 2000 / 35 armor on the bunker.
        self::assertSame(70, $defendingSoldiers->getAmount());
        self::assertSame(1943, $bunker->getHealth());
        self::assertContains(
            'Attacking Artillery bombard the defending Bunker (level 1) for 2000 damage',
            $battlePhase->getBattleLog()
        );
        self::assertContains('Defending Bunker (level 1) shelters 50 of 100 troops', $battlePhase->getBattleLog());
    }

    public function testArtilleryCanDestroyBunker(): void
    {
        $attackingArtillery = $this->createFleetUnit(GameUnitEnum::ARTILLERY, 80);
        $defendingSoldiers = $this->createStackableUnit(GameUnitEnum::SOLDIER, 100);
        $bunker = $this->createLeveledUnit(GameUnitEnum::BUNKER, 1, 4000);

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
