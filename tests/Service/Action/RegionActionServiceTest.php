<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources as PlayerResources;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RegionActionServiceTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function waterTypeProvider(): array
    {
        return array_combine(WorldRegion::WATER_TYPES, array_map(
            static fn (string $type): array => [$type],
            WorldRegion::WATER_TYPES
        ));
    }

    #[DataProvider('waterTypeProvider')]
    public function testBuyWorldRegionRejectsWater(string $type): void
    {
        $player = $this->createPlayer();
        $region = WorldRegion::createForWorld($player->getWorld(), 1, 1, 0, $type, 0);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Water regions cannot be bought!');

        try {
            $this->createService($region)->buyWorldRegion(1, $player);
        } finally {
            self::assertNull($region->getPlayer());
            self::assertSame(1000000, $player->getResources()->getCash());
        }
    }

    public function testBuyWorldRegionBuysLand(): void
    {
        $player = $this->createPlayer();
        $region = WorldRegion::createForWorld($player->getWorld(), 1, 1, 0, WorldRegion::TYPE_SAND, 500);

        $this->createService($region)->buyWorldRegion(1, $player);

        self::assertSame($player, $region->getPlayer());
    }

    private function createPlayer(): Player
    {
        $world = new World();
        $world->setId(1);

        $resources = new PlayerResources();
        $resources->setCash(1000000);

        $player = new Player();
        $player->setWorld($world);
        $player->setResources($resources);

        return $player;
    }

    private function createService(WorldRegion $region): RegionActionService
    {
        $worldRegionRepository = $this->createMock(WorldRegionRepository::class);
        $worldRegionRepository->method('find')->willReturn($region);

        return new RegionActionService(
            $worldRegionRepository,
            $this->createMock(PlayerRepository::class),
            $this->createMock(FederationRepository::class),
            new DistanceCalculator(0),
            new GameUnitRegistry()
        );
    }
}
