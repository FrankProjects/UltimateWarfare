<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\FleetMovement;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\FleetRouteService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FleetRouteServiceTest extends TestCase
{
    private World $world;
    private Player $player;
    private Player $enemy;
    private int $nextRegionId = 1;

    protected function setUp(): void
    {
        $this->world = new World();
        $this->world->setId(1);

        $this->player = new Player();
        $this->player->setId(1);
        $this->player->setWorld($this->world);

        $this->enemy = new Player();
        $this->enemy->setId(2);
        $this->enemy->setWorld($this->world);
    }

    public function testGroundRouteMarchesAroundEnemyRegion(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 0, $this->enemy);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 1, $this->player);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 1, $this->player);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->player);

        self::assertSame(
            [[0, 0], [0, 1], [1, 1], [2, 0]],
            (new FleetRouteService())->findRoute($source, $target, $this->player, FleetMovement::GROUND)
        );
    }

    public function testGroundRouteNeedsConnectedOwnRegions(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 0, null);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->player);

        self::assertNull(
            (new FleetRouteService())->findRoute($source, $target, $this->player, FleetMovement::GROUND)
        );
    }

    public function testGroundAttackReachesEnemyNextToYourRegions(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 0, $this->player);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->enemy);

        self::assertSame(
            [[0, 0], [1, 0], [2, 0]],
            (new FleetRouteService())->findRoute($source, $target, $this->player, FleetMovement::GROUND)
        );
    }

    public function testGroundAttackDoesNotMarchThroughEnemyRegions(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 0, $this->enemy);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->enemy);

        self::assertNull(
            (new FleetRouteService())->findRoute($source, $target, $this->player, FleetMovement::GROUND)
        );
    }

    public function testShipsSailAroundLand(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);
        $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_WATER, 1, 1, $this->player);
        $this->createRegion(WorldRegion::TYPE_WATER, 2, 1, null);
        $target = $this->createRegion(WorldRegion::TYPE_SAND, 3, 0, $this->player);

        self::assertSame(
            [[0, 0], [1, 0], [1, 1], [2, 1], [3, 0]],
            (new FleetRouteService())->findRoute($source, $target, $this->player, FleetMovement::NAVAL)
        );
    }

    public function testShipsDoNotSailOverLandOrEnemyWater(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $neighbor = $this->createRegion(WorldRegion::TYPE_SAND, 1, 0, $this->player);
        $this->createRegion(WorldRegion::TYPE_WATER, 0, 1, $this->enemy);
        $target = $this->createRegion(WorldRegion::TYPE_SAND, 1, 1, $this->player);

        $fleetRouteService = new FleetRouteService();
        self::assertNull($fleetRouteService->findRoute($source, $neighbor, $this->player, FleetMovement::NAVAL));
        self::assertNull($fleetRouteService->findRoute($source, $target, $this->player, FleetMovement::NAVAL));
    }

    public function testAircraftFlyStraight(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 5, 3, $this->enemy);

        self::assertSame(
            [[0, 0], [5, 3]],
            (new FleetRouteService())->findRoute($source, $target, $this->player, FleetMovement::AIR)
        );
    }

    public function testFindSourceRegionsOfGroundAttack(): void
    {
        $far = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 0, 0, $this->player);
        $border = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 1, 0, $this->player);
        $cutOff = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 4, 0, $this->player);
        $target = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, $this->enemy);

        $sources = (new FleetRouteService())->findSourceRegions($target, $this->player, FleetMovement::GROUND);

        self::assertSame([$border->getId() => 1, $far->getId() => 2], $sources);
        self::assertArrayNotHasKey($cutOff->getId(), $sources);
    }

    public function testFindTargetRegionsOfShipsIncludesOpenSea(): void
    {
        $source = $this->createRegion(WorldRegion::TYPE_SAND, 0, 0, $this->player);
        $openSea = $this->createRegion(WorldRegion::TYPE_WATER, 1, 0, null);
        $inland = $this->createRegion(WorldRegion::TYPE_GRASSLAND, 2, 0, null);

        $targets = (new FleetRouteService())->findTargetRegions($source, $this->player, FleetMovement::NAVAL);

        self::assertSame([$openSea->getId() => 1], $targets);
        self::assertArrayNotHasKey($inland->getId(), $targets);
    }

    public function testShipsAndGroundTroopsCannotTravelTogether(): void
    {
        $gameUnitRegistry = new GameUnitRegistry();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Ships and ground troops can't travel together");

        (new FleetRouteService())->getMovement([
            $gameUnitRegistry->find(GameUnitEnum::CRUISER),
            $gameUnitRegistry->find(GameUnitEnum::SOLDIER),
        ]);
    }

    public function testMovementFollowsUnits(): void
    {
        $gameUnitRegistry = new GameUnitRegistry();
        $fleetRouteService = new FleetRouteService();

        self::assertSame(FleetMovement::AIR, $fleetRouteService->getMovement([
            $gameUnitRegistry->find(GameUnitEnum::FIGHTER),
        ]));
        self::assertSame(FleetMovement::GROUND, $fleetRouteService->getMovement([
            $gameUnitRegistry->find(GameUnitEnum::FIGHTER),
            $gameUnitRegistry->find(GameUnitEnum::TANK),
        ]));
        self::assertSame(FleetMovement::NAVAL, $fleetRouteService->getMovement([
            $gameUnitRegistry->find(GameUnitEnum::CRUISER),
        ]));
    }

    private function createRegion(string $type, int $x, int $y, ?Player $player): WorldRegion
    {
        $region = WorldRegion::createForWorld($this->world, $x, $y, 0, $type, 0);
        $region->setId($this->nextRegionId++);
        $region->setPlayer($player);
        $player?->getWorldRegions()->add($region);
        $this->world->getWorldRegions()->add($region);

        return $region;
    }
}
