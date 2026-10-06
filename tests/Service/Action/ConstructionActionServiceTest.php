<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use Doctrine\Common\Collections\ArrayCollection;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources as PlayerResources;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\Action\ConstructionActionService;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorFactory;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorInterface;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConstructionActionServiceTest extends TestCase
{
    private ConstructionActionService $service;
    private ConstructionRepository&MockObject $constructionRepository;

    protected function setUp(): void
    {
        // NetWorthUpdaterService and NetWorthCalculator are final; build real instances
        // from mocked repositories. They are not reached by the leveled-category guard.
        $netWorthCalculator = new NetWorthCalculator(
            $this->createMock(WorldRegionStackableUnitRepository::class),
            $this->createMock(WorldRegionLeveledUnitRepository::class)
        );
        $netWorthUpdaterService = new NetWorthUpdaterService(
            $this->createMock(FederationRepository::class),
            $this->createMock(PlayerRepository::class),
            $netWorthCalculator
        );

        $behavior = $this->createMock(GameUnitBehaviorInterface::class);
        $behavior->method('canBuild')->willReturn(true);
        $behaviorFactory = $this->createMock(GameUnitBehaviorFactory::class);
        $behaviorFactory->method('create')->willReturn($behavior);

        $this->constructionRepository = $this->createMock(ConstructionRepository::class);

        $this->service = new ConstructionActionService(
            $this->constructionRepository,
            new GameUnitRegistry(),
            $this->createMock(PlayerRepository::class),
            $this->createMock(WorldRegionStackableUnitRepository::class),
            $netWorthUpdaterService,
            $behaviorFactory
        );
    }

    public function testConstructRejectsLeveledCategory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('dedicated building endpoints');

        $this->service->constructGameUnits(
            $this->createMock(WorldRegion::class),
            $this->createMock(Player::class),
            GameUnitCategory::DEFENSE_BUILDINGS,
            [GameUnitCategory::DEFENSE_BUILDINGS->value => '1']
        );
    }

    public function testRemoveRejectsLeveledCategory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot be destroyed');

        $this->service->removeGameUnits(
            $this->createMock(WorldRegion::class),
            $this->createMock(Player::class),
            GameUnitCategory::SPECIAL_BUILDINGS,
            [GameUnitCategory::SPECIAL_BUILDINGS->value => '1']
        );
    }

    public function testConstructAddsConstructionsToRegion(): void
    {
        $region = $this->createRegion(10);

        $this->constructionRepository->expects(self::once())->method('save')
            ->with(self::isInstanceOf(Construction::class));

        $this->service->constructGameUnits(
            $region,
            $this->createPlayer(),
            GameUnitCategory::BUILDINGS,
            [GameUnitEnum::FARM->value => '5']
        );

        $constructions = $region->getConstructions()->toArray();
        self::assertCount(1, $constructions);
        self::assertSame(GameUnitEnum::FARM, $constructions[0]->getGameUnit());
        self::assertSame(5, $constructions[0]->getNumber());
    }

    public function testFailedConstructDoesNotAddConstructionsToRegion(): void
    {
        $region = $this->createRegion(10);

        $this->constructionRepository->expects(self::never())->method('save');

        try {
            $this->service->constructGameUnits(
                $region,
                $this->createPlayer(),
                GameUnitCategory::BUILDINGS,
                [GameUnitEnum::FARM->value => '50']
            );
            self::fail('Expected building space to be exceeded');
        } catch (RuntimeException $e) {
            self::assertSame('You do not have that much building space.', $e->getMessage());
        }

        self::assertTrue($region->getConstructions()->isEmpty());
    }

    private function createRegion(int $space): WorldRegion
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setSpace($space);

        return $region;
    }

    private function createPlayer(): Player
    {
        $resources = new PlayerResources();
        $resources->setCash(1000000);
        $resources->setWood(1000000);
        $resources->setSteel(1000000);

        $player = $this->createMock(Player::class);
        $player->method('getResources')->willReturn($resources);
        $player->method('getPlayerResearch')->willReturn(new ArrayCollection([]));

        return $player;
    }
}
