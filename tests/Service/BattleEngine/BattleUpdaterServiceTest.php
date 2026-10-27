<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\BattleEngine;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleUpdaterService;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use FrankProjects\UltimateWarfare\Service\MoraleUpdaterService;
use FrankProjects\UltimateWarfare\Service\WaterRegionUpdaterService;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BattleUpdaterServiceTest extends TestCase
{
    public function testBattleLostOnlyRemovesDefendersThatDied(): void
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setPlayer(new Player());
        $survivingSoldiers = WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);
        $deadTanks = WorldRegionStackableUnit::create($region, GameUnitEnum::TANK, 5);
        $survivingBarrack = WorldRegionLeveledUnit::create($region, GameUnitEnum::BARRACK, 1, 6000);
        $destroyedAirfield = WorldRegionLeveledUnit::create($region, GameUnitEnum::AIRFIELD, 1, 6000);

        $fleet = new Fleet();
        $fleet->setTargetWorldRegion($region);
        $fleet->setPlayer(new Player());

        $stackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $stackableUnitRepository->expects(self::once())->method('remove')->with($deadTanks);
        $stackableUnitRepository->expects(self::once())->method('save')->with($survivingSoldiers);

        $leveledUnitRepository = $this->createMock(WorldRegionLeveledUnitRepository::class);
        $leveledUnitRepository->expects(self::once())->method('remove')->with($destroyedAirfield);
        $leveledUnitRepository->expects(self::once())->method('save')->with($survivingBarrack);

        $service = new BattleUpdaterService(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class),
            $this->createMock(WorldRegionRepository::class),
            $stackableUnitRepository,
            $leveledUnitRepository,
            $this->createMock(ConstructionRepository::class),
            $this->createMock(PlayerRepository::class),
            $this->createWaterRegionUpdaterService(),
            new GameUnitRegistry(),
            new MoraleUpdaterService(new GameUnitRegistry())
        );

        $service->updateBattleLost($fleet, [], [$survivingSoldiers, $survivingBarrack]);
    }

    public function testBattleWonRemovesConstructionsOfPreviousOwner(): void
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setPlayer(new Player());
        $defenderConstruction = Construction::create($region, new Player(), GameUnitEnum::SOLDIER, 10, 60);

        $fleet = new Fleet();
        $fleet->setWorldRegion(new WorldRegion());
        $fleet->setTargetWorldRegion($region);
        $fleet->setPlayer(new Player());

        $constructionRepository = $this->createMock(ConstructionRepository::class);
        $constructionRepository->expects(self::once())->method('remove')->with($defenderConstruction);

        $service = new BattleUpdaterService(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class),
            $this->createMock(WorldRegionRepository::class),
            $this->createMock(WorldRegionStackableUnitRepository::class),
            $this->createMock(WorldRegionLeveledUnitRepository::class),
            $constructionRepository,
            $this->createMock(PlayerRepository::class),
            $this->createWaterRegionUpdaterService(),
            new GameUnitRegistry(),
            new MoraleUpdaterService(new GameUnitRegistry())
        );

        $service->updateBattleWon($fleet, []);
    }

    public function testBattleWonReturnsAircraftToSourceRegion(): void
    {
        $attacker = new Player();
        $source = new WorldRegion();
        $source->setPlayer($attacker);
        $sourceFighters = WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 3);
        $target = $this->createOwnedRegion();

        $fleet = new Fleet();
        $fleet->setPlayer($attacker);
        $fleet->setWorldRegion($source);
        $fleet->setTargetWorldRegion($target);
        $fighters = FleetUnit::createForFleet($fleet, GameUnitEnum::FIGHTER, 2);
        $soldiers = FleetUnit::createForFleet($fleet, GameUnitEnum::SOLDIER, 10);

        $this->createServiceForFleet(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class)
        )->updateBattleWon($fleet, [$fighters, $soldiers]);

        self::assertSame(5, $sourceFighters->getAmount());
        self::assertSame($attacker, $target->getPlayer());
        $targetUnits = $target->getWorldRegionStackableUnits()->toArray();
        self::assertCount(1, $targetUnits);
        self::assertSame(GameUnitEnum::SOLDIER, $targetUnits[0]->getGameUnit());
    }

    public function testBattleWonGivesConqueredRegionTheFleetMoraleAndMergesReturningAircraft(): void
    {
        $attacker = new Player();
        $source = new WorldRegion();
        $source->setPlayer($attacker);
        $source->setMorale(100);
        $source->setMoraleTimestamp(time());
        WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 2);
        $target = $this->createOwnedRegion();

        $fleet = new Fleet();
        $fleet->setPlayer($attacker);
        $fleet->setWorldRegion($source);
        $fleet->setTargetWorldRegion($target);
        $fleet->setMorale(60);
        $fighters = FleetUnit::createForFleet($fleet, GameUnitEnum::FIGHTER, 2);
        $soldiers = FleetUnit::createForFleet($fleet, GameUnitEnum::SOLDIER, 10);

        $this->createServiceForFleet(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class)
        )->updateBattleWon($fleet, [$fighters, $soldiers]);

        self::assertSame(60, $target->getMorale());
        // 2 Fighters at 100 and 2 returning Fighters at 60
        self::assertSame(80, $source->getMorale());
    }

    public function testBattleWonKeepsAircraftOnTargetWhenSourceRegionIsLost(): void
    {
        $attacker = new Player();
        $source = $this->createOwnedRegion();
        $target = $this->createOwnedRegion();

        $fleet = new Fleet();
        $fleet->setPlayer($attacker);
        $fleet->setWorldRegion($source);
        $fleet->setTargetWorldRegion($target);
        $fighters = FleetUnit::createForFleet($fleet, GameUnitEnum::FIGHTER, 2);

        $this->createServiceForFleet(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class)
        )->updateBattleWon($fleet, [$fighters]);

        self::assertCount(0, $source->getWorldRegionStackableUnits());
        self::assertCount(1, $target->getWorldRegionStackableUnits());
    }

    public function testBattleLostKeepsSurvivingAttackersInFleet(): void
    {
        $fleet = new Fleet();
        $fleet->setTargetWorldRegion($this->createOwnedRegion());
        $fleet->setPlayer(new Player());
        $survivingSoldiers = FleetUnit::createForFleet($fleet, GameUnitEnum::SOLDIER, 10);
        $deadTanks = FleetUnit::createForFleet($fleet, GameUnitEnum::TANK, 5);
        $fleet->getFleetUnits()->add($survivingSoldiers);
        $fleet->getFleetUnits()->add($deadTanks);

        $fleetRepository = $this->createMock(FleetRepository::class);
        $fleetRepository->expects(self::never())->method('remove');

        $fleetUnitRepository = $this->createMock(FleetUnitRepository::class);
        $fleetUnitRepository->expects(self::once())->method('remove')->with($deadTanks);
        $fleetUnitRepository->expects(self::once())->method('save')->with($survivingSoldiers);

        $service = $this->createServiceForFleet($fleetRepository, $fleetUnitRepository);
        $service->updateBattleLost($fleet, [$survivingSoldiers], []);

        self::assertSame([$survivingSoldiers], array_values($fleet->getFleetUnits()->toArray()));
    }

    public function testBattleLostRemovesFleetWithoutSurvivors(): void
    {
        $fleet = new Fleet();
        $fleet->setTargetWorldRegion($this->createOwnedRegion());
        $fleet->setPlayer(new Player());
        $fleet->getFleetUnits()->add(FleetUnit::createForFleet($fleet, GameUnitEnum::SOLDIER, 10));

        $fleetRepository = $this->createMock(FleetRepository::class);
        $fleetRepository->expects(self::once())->method('remove')->with($fleet);

        $fleetUnitRepository = $this->createMock(FleetUnitRepository::class);
        $fleetUnitRepository->expects(self::never())->method('save');

        $service = $this->createServiceForFleet($fleetRepository, $fleetUnitRepository);
        $service->updateBattleLost($fleet, [], []);
    }

    public function testBattleWonNotifiesOnlyPreviousOwner(): void
    {
        $attacker = new Player();
        $defender = new Player();
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setPlayer($defender);

        $fleet = new Fleet();
        $fleet->setWorldRegion(new WorldRegion());
        $fleet->setTargetWorldRegion($region);
        $fleet->setPlayer($attacker);

        $playerRepository = $this->createMock(PlayerRepository::class);
        $playerRepository->expects(self::once())->method('save')->with($defender);

        $service = $this->createServiceForPlayers($playerRepository);
        $service->updateBattleWon($fleet, []);

        self::assertFalse($attacker->getNotifications()->getAttacked());
        self::assertTrue($defender->getNotifications()->getAttacked());
        self::assertSame($attacker, $region->getPlayer());
    }

    public function testBattleLostNotifiesOnlyDefender(): void
    {
        $attacker = new Player();
        $defender = new Player();
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setPlayer($defender);

        $fleet = new Fleet();
        $fleet->setWorldRegion(new WorldRegion());
        $fleet->setTargetWorldRegion($region);
        $fleet->setPlayer($attacker);

        $playerRepository = $this->createMock(PlayerRepository::class);
        $playerRepository->expects(self::once())->method('save')->with($defender);

        $service = $this->createServiceForPlayers($playerRepository);
        $service->updateBattleLost($fleet, [], []);

        self::assertFalse($attacker->getNotifications()->getAttacked());
        self::assertTrue($defender->getNotifications()->getAttacked());
    }

    public function testBattleThrowsForRegionWithoutOwner(): void
    {
        $fleet = new Fleet();
        $fleet->setTargetWorldRegion(new WorldRegion());
        $fleet->setPlayer(new Player());

        $service = $this->createServiceForPlayers($this->createMock(PlayerRepository::class));

        $this->expectException(RuntimeException::class);
        $service->updateBattleLost($fleet, [], []);
    }

    private function createOwnedRegion(): WorldRegion
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setPlayer(new Player());

        return $region;
    }

    private function createServiceForPlayers(PlayerRepository $playerRepository): BattleUpdaterService
    {
        return new BattleUpdaterService(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class),
            $this->createMock(WorldRegionRepository::class),
            $this->createMock(WorldRegionStackableUnitRepository::class),
            $this->createMock(WorldRegionLeveledUnitRepository::class),
            $this->createMock(ConstructionRepository::class),
            $playerRepository,
            $this->createWaterRegionUpdaterService(),
            new GameUnitRegistry(),
            new MoraleUpdaterService(new GameUnitRegistry())
        );
    }

    private function createServiceForFleet(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository
    ): BattleUpdaterService {
        return new BattleUpdaterService(
            $fleetRepository,
            $fleetUnitRepository,
            $this->createMock(WorldRegionRepository::class),
            $this->createMock(WorldRegionStackableUnitRepository::class),
            $this->createMock(WorldRegionLeveledUnitRepository::class),
            $this->createMock(ConstructionRepository::class),
            $this->createMock(PlayerRepository::class),
            $this->createWaterRegionUpdaterService(),
            new GameUnitRegistry(),
            new MoraleUpdaterService(new GameUnitRegistry())
        );
    }

    private function createWaterRegionUpdaterService(): WaterRegionUpdaterService
    {
        return new WaterRegionUpdaterService(
            $this->createMock(WorldRegionRepository::class),
            new NetWorthUpdaterService(
                $this->createMock(FederationRepository::class),
                $this->createMock(PlayerRepository::class),
                new NetWorthCalculator(
                    $this->createMock(WorldRegionStackableUnitRepository::class),
                    $this->createMock(WorldRegionLeveledUnitRepository::class)
                )
            ),
            $this->createMock(EntityLockRepository::class),
            $this->createMock(EntityManagerInterface::class)
        );
    }

    public function testBattleLostReleasesWaterRegionWithoutSurvivingDefenders(): void
    {
        $defender = new Player();
        $region = $this->createDefendedWaterRegion($defender);

        $this->createServiceForWaterRegion()->updateBattleLost($this->createFleetAttacking($region), [], []);

        self::assertNull($region->getPlayer());
        self::assertFalse($defender->getWorldRegions()->contains($region));
    }

    public function testBattleLostKeepsWaterRegionWithSurvivingDefenders(): void
    {
        $defender = new Player();
        $region = $this->createDefendedWaterRegion($defender);
        $survivingCruisers = $region->getWorldRegionStackableUnits()->toArray();

        $this->createServiceForWaterRegion()->updateBattleLost(
            $this->createFleetAttacking($region),
            [],
            $survivingCruisers
        );

        self::assertSame($defender, $region->getPlayer());
    }

    private function createDefendedWaterRegion(Player $defender): WorldRegion
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_WATER);
        $region->setPlayer($defender);
        $defender->getWorldRegions()->add($region);
        WorldRegionStackableUnit::create($region, GameUnitEnum::CRUISER, 2);

        return $region;
    }

    private function createFleetAttacking(WorldRegion $region): Fleet
    {
        $fleet = new Fleet();
        $fleet->setTargetWorldRegion($region);
        $fleet->setPlayer(new Player());

        return $fleet;
    }

    private function createServiceForWaterRegion(): BattleUpdaterService
    {
        // Like the Doctrine repository, removing a unit also removes it from the region's collection
        $stackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $stackableUnitRepository->method('remove')->willReturnCallback(
            static function (WorldRegionStackableUnit $unit): void {
                $unit->getWorldRegion()->removeWorldRegionStackableUnit($unit);
            }
        );

        return new BattleUpdaterService(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class),
            $this->createMock(WorldRegionRepository::class),
            $stackableUnitRepository,
            $this->createMock(WorldRegionLeveledUnitRepository::class),
            $this->createMock(ConstructionRepository::class),
            $this->createMock(PlayerRepository::class),
            $this->createWaterRegionUpdaterService(),
            new GameUnitRegistry(),
            new MoraleUpdaterService(new GameUnitRegistry())
        );
    }
}
