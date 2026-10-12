<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleUpdaterService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BattleUpdaterServiceTest extends TestCase
{
    public function testBattleLostOnlyRemovesDefendersThatDied(): void
    {
        $region = new WorldRegion();
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
            $this->createMock(PlayerRepository::class)
        );

        $service->updateBattleLost($fleet, [], [$survivingSoldiers, $survivingBarrack]);
    }

    public function testBattleWonRemovesConstructionsOfPreviousOwner(): void
    {
        $region = new WorldRegion();
        $region->setPlayer(new Player());
        $defenderConstruction = Construction::create($region, new Player(), GameUnitEnum::SOLDIER, 10, 60);

        $fleet = new Fleet();
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
            $this->createMock(PlayerRepository::class)
        );

        $service->updateBattleWon($fleet, []);
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
        $region->setPlayer($defender);

        $fleet = new Fleet();
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
        $region->setPlayer($defender);

        $fleet = new Fleet();
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
            $playerRepository
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
            $this->createMock(PlayerRepository::class)
        );
    }
}
