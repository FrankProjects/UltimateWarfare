<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\Action\FleetActionService;
use FrankProjects\UltimateWarfare\Service\FleetFactory;
use FrankProjects\UltimateWarfare\Service\FleetRouteService;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use FrankProjects\UltimateWarfare\Service\WaterRegionUpdaterService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FleetActionServiceTest extends TestCase
{
    private World $world;
    private Player $player;
    private WorldRegionRepository&MockObject $worldRegionRepository;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->world = new World();
        $this->world->setId(1);

        $this->player = new Player();
        $this->player->setId(1);
        $this->player->setWorld($this->world);

        $this->worldRegionRepository = $this->createMock(WorldRegionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public function testSendRejectsNonNavalUnitsToWater(): void
    {
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($harbor, GameUnitEnum::SOLDIER, 10);
        $openSea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only naval units can be sent to water!');

        $this->createService()->sendGameUnits(
            $harbor,
            $openSea,
            $this->player,
            [GameUnitEnum::SOLDIER->value => '10']
        );
    }

    public function testSendRejectsOpenSeaNotNextToYourRegions(): void
    {
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($harbor, GameUnitEnum::CRUISER, 2);
        $openSea = $this->createRegion(WorldRegion::TYPE_WATER, 5, 5, null);
        $this->worldRegionRepository->method('findAdjacentRegions')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You can only sail to open sea next to one of your regions!');

        $this->createService()->sendGameUnits(
            $harbor,
            $openSea,
            $this->player,
            [GameUnitEnum::CRUISER->value => '2']
        );
    }

    public function testSendShipsToOpenSeaNextToYourRegion(): void
    {
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($harbor, GameUnitEnum::CRUISER, 2);
        $openSea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);
        $this->worldRegionRepository->method('findAdjacentRegions')->willReturn([$harbor]);

        $fleet = $this->createService()->sendGameUnits(
            $harbor,
            $openSea,
            $this->player,
            [GameUnitEnum::CRUISER->value => '2']
        );

        self::assertSame($openSea, $fleet->getTargetWorldRegion());
        // A land region is never released
        self::assertSame($this->player, $harbor->getPlayer());
    }

    public function testSendingLastShipsReleasesWaterRegion(): void
    {
        $sea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, $this->player);
        WorldRegionStackableUnit::create($sea, GameUnitEnum::CRUISER, 3);
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);

        $this->createService()->sendGameUnits(
            $sea,
            $harbor,
            $this->player,
            [GameUnitEnum::CRUISER->value => '3']
        );

        self::assertNull($sea->getPlayer());
        self::assertFalse($this->player->getWorldRegions()->contains($sea));
    }

    public function testSendingSomeShipsKeepsWaterRegion(): void
    {
        $sea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, $this->player);
        WorldRegionStackableUnit::create($sea, GameUnitEnum::CRUISER, 3);
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);

        $this->createService()->sendGameUnits(
            $sea,
            $harbor,
            $this->player,
            [GameUnitEnum::CRUISER->value => '2']
        );

        self::assertSame($this->player, $sea->getPlayer());
    }

    public function testReinforceClaimsOpenSea(): void
    {
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $openSea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);
        $this->createFleet($harbor, $openSea, time() - 1);

        $region = $this->createService()->reinforce(1, $this->player);

        self::assertSame($openSea, $region);
        self::assertSame($this->player, $openSea->getPlayer());
        self::assertTrue($this->player->getWorldRegions()->contains($openSea));
        $stationedUnit = $openSea->getWorldRegionStackableUnits()->first();
        self::assertNotFalse($stationedUnit);
        self::assertSame(GameUnitEnum::CRUISER, $stationedUnit->getGameUnit());
        self::assertSame(2, $stationedUnit->getAmount());
    }

    public function testReinforceRejectsOpenSeaBeforeArrival(): void
    {
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $openSea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);
        $this->createFleet($harbor, $openSea, time() + 600);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fleet not arrived yet!');

        try {
            $this->createService()->reinforce(1, $this->player);
        } finally {
            self::assertNull($openSea->getPlayer());
        }
    }

    public function testReinforceRejectsWaterRegionHeldByEnemy(): void
    {
        $enemy = new Player();
        $enemy->setId(2);
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $enemySea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, $enemy);
        $this->createFleet($harbor, $enemySea, time() - 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You are not the owner of this region!');

        $this->createService()->reinforce(1, $this->player);
    }

    public function testRecallClaimsReleasedWaterRegionAgain(): void
    {
        $releasedSea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);
        $harbor = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $this->createFleet($releasedSea, $harbor, time() + 600);

        $region = $this->createService()->recall(1, $this->player);

        self::assertSame($releasedSea, $region);
        self::assertSame($this->player, $releasedSea->getPlayer());
    }

    public function testSendRejectsAircraftFromRegionWithoutAirfield(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 2);
        $enemyRegion = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->createEnemy());

        $this->expectExceptionMessage('Aircraft cannot take off');

        $this->createService()->sendGameUnits(
            $source,
            $enemyRegion,
            $this->player,
            [GameUnitEnum::FIGHTER->value => '2']
        );
    }

    public function testSendRejectsAircraftToOwnRegionWithoutAirfield(): void
    {
        $source = $this->createAirfieldRegion(0, 0);
        WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 2);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->player);

        $this->expectExceptionMessage('Aircraft can only be moved to a region with an Airfield!');

        $this->createService()->sendGameUnits($source, $target, $this->player, [GameUnitEnum::FIGHTER->value => '2']);
    }

    public function testSendAircraftBetweenAirfields(): void
    {
        $source = $this->createAirfieldRegion(0, 0);
        WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 2);
        $target = $this->createAirfieldRegion(2, 0);

        $fleet = $this->createService()->sendGameUnits(
            $source,
            $target,
            $this->player,
            [GameUnitEnum::FIGHTER->value => '2']
        );

        self::assertSame($target, $fleet->getTargetWorldRegion());
    }

    public function testSendTroopsAlongYourRegions(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($source, GameUnitEnum::SOLDIER, 10);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 0, $this->createEnemy());
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 1, $this->player);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 1, $this->player);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->player);

        $fleet = $this->createService()->sendGameUnits(
            $source,
            $target,
            $this->player,
            [GameUnitEnum::SOLDIER->value => '10']
        );

        self::assertSame([[0, 0], [0, 1], [1, 1], [2, 0]], $fleet->getPath());
        // 3 steps of 200 seconds at base speed
        self::assertSame(600, $fleet->getTimestampArrive() - $fleet->getTimestamp());
    }

    public function testSendRejectsTroopsToRegionNotConnectedToSource(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($source, GameUnitEnum::SOLDIER, 10);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 3, 0, $this->player);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Your units can't reach this region");

        $this->createService()->sendGameUnits(
            $source,
            $target,
            $this->player,
            [GameUnitEnum::SOLDIER->value => '10']
        );
    }

    public function testSendRejectsTroopsAttackingRegionNotNextToYourRegions(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        WorldRegionStackableUnit::create($source, GameUnitEnum::TANK, 5);
        $enemyRegion = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->createEnemy());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Your units can't reach this region");

        $this->createService()->sendGameUnits(
            $source,
            $enemyRegion,
            $this->player,
            [GameUnitEnum::TANK->value => '5']
        );
    }

    public function testAircraftFlyStraightToAirfieldNotConnectedToSource(): void
    {
        $source = $this->createAirfieldRegion(0, 0);
        WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 2);
        $target = $this->createAirfieldRegion(3, 0);

        $fleet = $this->createService()->sendGameUnits(
            $source,
            $target,
            $this->player,
            [GameUnitEnum::FIGHTER->value => '2']
        );

        self::assertSame([[0, 0], [3, 0]], $fleet->getPath());
    }

    public function testAttackWithAircraftNeedsNoAirfieldOnTarget(): void
    {
        $source = $this->createAirfieldRegion(0, 0);
        WorldRegionStackableUnit::create($source, GameUnitEnum::FIGHTER, 2);
        $enemyRegion = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->createEnemy());

        $fleet = $this->createService()->sendGameUnits(
            $source,
            $enemyRegion,
            $this->player,
            [GameUnitEnum::FIGHTER->value => '2']
        );

        self::assertSame($enemyRegion, $fleet->getTargetWorldRegion());
    }

    public function testReinforceRejectsAircraftWhenTargetLostItsAirfield(): void
    {
        $source = $this->createAirfieldRegion(0, 0);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->player);
        $fleet = $this->createFleet($source, $target, time());
        $fleet->getFleetUnits()->add(FleetUnit::createForFleet($fleet, GameUnitEnum::FIGHTER, 2));

        $this->expectExceptionMessage('Aircraft cannot land');

        $this->createService()->reinforce(1, $this->player);
    }

    private function createAirfieldRegion(int $x, int $y): WorldRegion
    {
        $region = $this->createRegion(WorldRegion::TYPE_GRASSLAND, $x, $y, $this->player);
        WorldRegionLeveledUnit::create($region, GameUnitEnum::AIRFIELD, 1, 6000);

        return $region;
    }

    private function createEnemy(): Player
    {
        $enemy = new Player();
        $enemy->setId(2);
        $enemy->setWorld($this->world);

        return $enemy;
    }

    private function createRegion(string $type, int $x, int $y, ?Player $player): WorldRegion
    {
        $region = WorldRegion::createForWorld($this->world, $x, $y, 0, $type, 0);
        $region->setPlayer($player);
        $player?->getWorldRegions()->add($region);
        $this->world->getWorldRegions()->add($region);

        return $region;
    }

    /**
     * A fleet of 2 Cruisers, which the mocked EntityManager returns for any id
     */
    private function createFleet(WorldRegion $source, WorldRegion $target, int $timestampArrive): Fleet
    {
        $fleet = new Fleet();
        $fleet->setPlayer($this->player);
        $fleet->setWorldRegion($source);
        $fleet->setTargetWorldRegion($target);
        $fleet->setTimestampArrive($timestampArrive);
        $fleet->getFleetUnits()->add(FleetUnit::createForFleet($fleet, GameUnitEnum::CRUISER, 2));

        $this->entityManager->method('find')->willReturn($fleet);

        return $fleet;
    }

    private function createService(): FleetActionService
    {
        // Like the Doctrine repository, removing a unit also removes it from the region's collection
        $stackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $stackableUnitRepository->method('remove')->willReturnCallback(
            static function (WorldRegionStackableUnit $unit): void {
                $unit->getWorldRegion()->removeWorldRegionStackableUnit($unit);
            }
        );

        $entityLockRepository = $this->createMock(EntityLockRepository::class);
        $entityLockRepository->method('lockForUpdate')->willReturn(true);

        $waterRegionUpdaterService = new WaterRegionUpdaterService(
            $this->worldRegionRepository,
            new NetWorthUpdaterService(
                $this->createMock(FederationRepository::class),
                $this->createMock(PlayerRepository::class),
                new NetWorthCalculator(
                    $stackableUnitRepository,
                    $this->createMock(WorldRegionLeveledUnitRepository::class)
                )
            ),
            $entityLockRepository,
            $this->entityManager
        );

        return new FleetActionService(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class),
            new GameUnitRegistry(),
            $stackableUnitRepository,
            $this->entityManager,
            new FleetFactory(new DistanceCalculator(0)),
            $this->worldRegionRepository,
            $entityLockRepository,
            $waterRegionUpdaterService,
            new FleetRouteService()
        );
    }
}
