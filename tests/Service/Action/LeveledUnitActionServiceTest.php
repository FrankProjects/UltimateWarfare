<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use Doctrine\Common\Collections\ArrayCollection;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources as PlayerResources;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Service\Action\LeveledUnitActionService;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorFactory;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LeveledUnitActionServiceTest extends TestCase
{
    private LeveledUnitActionService $service;
    private ConstructionRepository&MockObject $constructionRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private PlayerRepository&MockObject $playerRepository;
    private WorldRegionLeveledUnitRepository&MockObject $leveledUnitRepository;
    private GameUnitBehaviorFactory&MockObject $behaviorFactory;

    protected function setUp(): void
    {
        $this->constructionRepository = $this->createMock(ConstructionRepository::class);
        $this->gameUnitRegistry = new GameUnitRegistry();
        $this->playerRepository = $this->createMock(PlayerRepository::class);
        $this->leveledUnitRepository = $this->createMock(WorldRegionLeveledUnitRepository::class);
        $this->behaviorFactory = $this->createMock(GameUnitBehaviorFactory::class);

        $behavior = $this->createMock(GameUnitBehaviorInterface::class);
        $behavior->method('canBuild')->willReturn(true);
        $behavior->method('getBuildRequirementDescription')->willReturn('');
        $this->behaviorFactory->method('create')->willReturn($behavior);

        $this->service = new LeveledUnitActionService(
            $this->constructionRepository,
            $this->gameUnitRegistry,
            $this->playerRepository,
            $this->leveledUnitRepository,
            $this->behaviorFactory
        );
    }

    private function createPlayer(int $cash = 1000000, int $wood = 1000000, int $steel = 1000000): Player
    {
        $resources = new PlayerResources();
        $resources->setCash($cash);
        $resources->setWood($wood);
        $resources->setSteel($steel);

        $player = $this->createMock(Player::class);
        $player->method('getResources')->willReturn($resources);
        $player->method('getPlayerResearch')->willReturn(new ArrayCollection([]));

        return $player;
    }

    /**
     * @param WorldRegionLeveledUnit[] $leveledUnits
     */
    private function createRegion(array $leveledUnits = []): WorldRegion
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setWorldRegionLeveledUnits(new ArrayCollection($leveledUnits));

        return $region;
    }

    private function createLeveledUnit(
        WorldRegion $region,
        GameUnitEnum $enum,
        int $level,
        int $health
    ): WorldRegionLeveledUnit {
        return WorldRegionLeveledUnit::create($region, $enum, $level, $health);
    }

    public function testBuildQueuesConstructionAtLevelOne(): void
    {
        $region = $this->createRegion();
        $player = $this->createPlayer();

        $this->constructionRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static fn (Construction $c): bool =>
                $c->getGameUnit() === GameUnitEnum::BARRACK && $c->getNumber() === 1));

        $this->service->build($region, $player, GameUnitEnum::BARRACK);

        // Barrack costs 2000 cash; the player started with 1,000,000.
        self::assertSame(998000, $player->getResources()->getCash());
    }

    public function testBuildRejectsWhenAlreadyPresent(): void
    {
        $region = $this->createRegion();
        $region->setWorldRegionLeveledUnits(
            new ArrayCollection([$this->createLeveledUnit($region, GameUnitEnum::BARRACK, 1, 6000)])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already built');

        $this->service->build($region, $this->createPlayer(), GameUnitEnum::BARRACK);
    }

    public function testBuildRejectsNonLeveledGameUnit(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not a leveled building');

        $this->service->build($this->createRegion(), $this->createPlayer(), GameUnitEnum::SOLDIER);
    }

    public function testUpgradeChargesEscalatingCost(): void
    {
        $region = $this->createRegion();
        $region->setWorldRegionLeveledUnits(
            new ArrayCollection([$this->createLeveledUnit($region, GameUnitEnum::BARRACK, 2, 12000)])
        );
        $player = $this->createPlayer();

        $this->constructionRepository->expects(self::once())->method('save');

        $this->service->upgrade($region, $player, GameUnitEnum::BARRACK);

        // Upgrading to level 3 costs 2000 * 3 = 6000 cash.
        self::assertSame(994000, $player->getResources()->getCash());
    }

    public function testUpgradeRejectedPastMaxLevel(): void
    {
        $region = $this->createRegion();
        $region->setWorldRegionLeveledUnits(
            new ArrayCollection([$this->createLeveledUnit($region, GameUnitEnum::BARRACK, 10, 60000)])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('maximum level');

        $this->service->upgrade($region, $this->createPlayer(), GameUnitEnum::BARRACK);
    }

    public function testUpgradeRejectedWhenNotBuilt(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not built');

        $this->service->upgrade($this->createRegion(), $this->createPlayer(), GameUnitEnum::BARRACK);
    }

    public function testRepairRestoresFullHealthAndScalesCost(): void
    {
        $region = $this->createRegion();
        // Barrack health is 6000 per level. At level 2 the max is 12000; half damaged.
        $leveledUnit = $this->createLeveledUnit($region, GameUnitEnum::BARRACK, 2, 6000);
        $region->setWorldRegionLeveledUnits(new ArrayCollection([$leveledUnit]));
        $player = $this->createPlayer();

        $this->constructionRepository->expects(self::never())->method('save');
        $this->leveledUnitRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static fn (WorldRegionLeveledUnit $u): bool => $u->getHealth() === 12000));

        $this->service->repair($region, $player, GameUnitEnum::BARRACK);

        self::assertSame(12000, $leveledUnit->getHealth());
        // Missing half of 12000 HP at level 2: ceil(2000 * 2 * 0.5) = 2000 cash.
        self::assertSame(998000, $player->getResources()->getCash());
    }

    public function testRepairRejectedAtFullHealth(): void
    {
        $region = $this->createRegion();
        $region->setWorldRegionLeveledUnits(
            new ArrayCollection([$this->createLeveledUnit($region, GameUnitEnum::BARRACK, 2, 12000)])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('full health');

        $this->service->repair($region, $this->createPlayer(), GameUnitEnum::BARRACK);
    }
}
