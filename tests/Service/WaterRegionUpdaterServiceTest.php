<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use FrankProjects\UltimateWarfare\Service\WaterRegionUpdaterService;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class WaterRegionUpdaterServiceTest extends TestCase
{
    public function testReleaseIfUnoccupiedReleasesEmptyWaterRegion(): void
    {
        $player = new Player();
        $region = $this->createRegion(WorldRegion::TYPE_WATER, $player);

        $this->createService()->releaseIfUnoccupied($region);

        self::assertNull($region->getPlayer());
        self::assertFalse($player->getWorldRegions()->contains($region));
        self::assertSame(0, $player->getNetWorth());
    }

    public function testReleaseIfUnoccupiedReleasesWaterRegionWithOnlyEmptyUnitStacks(): void
    {
        $region = $this->createRegion(WorldRegion::TYPE_DEEP_WATER, new Player());
        WorldRegionStackableUnit::create($region, GameUnitEnum::CRUISER, 0);

        $this->createService()->releaseIfUnoccupied($region);

        self::assertNull($region->getPlayer());
    }

    /**
     * @return array<string, array{callable(WorldRegion, Player): void}>
     */
    public static function occupiedRegionProvider(): array
    {
        return [
            'ships' => [
                static function (WorldRegion $region, Player $player): void {
                    WorldRegionStackableUnit::create($region, GameUnitEnum::CRUISER, 1);
                },
            ],
            'sea mine' => [
                static function (WorldRegion $region, Player $player): void {
                    WorldRegionLeveledUnit::create($region, GameUnitEnum::SEA_MINE, 1, 25);
                },
            ],
            'sea mine under construction' => [
                static function (WorldRegion $region, Player $player): void {
                    Construction::create($region, $player, GameUnitEnum::SEA_MINE, 1, 7200);
                },
            ],
        ];
    }

    /**
     * @param callable(WorldRegion, Player): void $occupy
     */
    #[DataProvider('occupiedRegionProvider')]
    public function testReleaseIfUnoccupiedKeepsOccupiedWaterRegion(callable $occupy): void
    {
        $player = new Player();
        $region = $this->createRegion(WorldRegion::TYPE_SHALLOW_WATER, $player);
        $occupy($region, $player);

        $this->createService()->releaseIfUnoccupied($region);

        self::assertSame($player, $region->getPlayer());
    }

    public function testReleaseIfUnoccupiedKeepsLandRegion(): void
    {
        $player = new Player();
        $region = $this->createRegion(WorldRegion::TYPE_SAND, $player);

        $this->createService()->releaseIfUnoccupied($region);

        self::assertSame($player, $region->getPlayer());
    }

    public function testClaimMakesOpenSeaYours(): void
    {
        $player = new Player();
        $region = $this->createRegion(WorldRegion::TYPE_WATER, null);

        $this->createService()->claim($region, $player);

        self::assertSame($player, $region->getPlayer());
        self::assertTrue($player->getWorldRegions()->contains($region));
        self::assertSame(NetWorthCalculator::NET_WORTH_CALCULATOR_REGION, $player->getNetWorth());
    }

    public function testClaimRefusesOwnedWaterRegion(): void
    {
        $owner = new Player();
        $region = $this->createRegion(WorldRegion::TYPE_WATER, $owner);

        $this->expectException(RuntimeException::class);
        $this->createService()->claim($region, new Player());
    }

    public function testClaimRefusesLandRegion(): void
    {
        $region = $this->createRegion(WorldRegion::TYPE_GRASSLAND, null);

        $this->expectException(RuntimeException::class);
        $this->createService()->claim($region, new Player());
    }

    private function createRegion(string $type, ?Player $player): WorldRegion
    {
        $region = new WorldRegion();
        $region->setType($type);
        $region->setPlayer($player);
        $player?->getWorldRegions()->add($region);

        return $region;
    }

    private function createService(): WaterRegionUpdaterService
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
}
