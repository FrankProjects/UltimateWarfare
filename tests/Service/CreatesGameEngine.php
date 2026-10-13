<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor\ConstructionProcessor;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor\ResearchProcessor;
use FrankProjects\UltimateWarfare\Service\IncomeUpdaterService;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use FrankProjects\UltimateWarfare\Util\IncomeCalculator;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;
use FrankProjects\UltimateWarfare\Util\UpkeepCalculator;
use PHPUnit\Framework\TestCase;

/**
 * GameEngine and its collaborators are final; build real instances on top of mocked repositories.
 * The mocked EntityManager and EntityLockRepository make transactions and row locks no-ops.
 *
 * @mixin TestCase
 */
trait CreatesGameEngine
{
    protected function createGameEngine(
        ?ConstructionRepository $constructionRepository = null,
        ?WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository = null,
        ?EntityManagerInterface $entityManager = null,
        ?EntityLockRepository $entityLockRepository = null
    ): GameEngine {
        if ($entityLockRepository === null) {
            // Every locked row still exists unless a test says otherwise
            $entityLockRepository = $this->createMock(EntityLockRepository::class);
            $entityLockRepository->method('lockForUpdate')->willReturn(true);
        }

        $gameUnitRegistry = new GameUnitRegistry();
        $constructionRepository ??= $this->createMock(ConstructionRepository::class);
        $worldRegionStackableUnitRepository ??= $this->createMock(WorldRegionStackableUnitRepository::class);
        $netWorthUpdaterService = $this->createNetWorthUpdaterService();

        return new GameEngine(
            new ConstructionProcessor(
                $constructionRepository,
                $this->createMock(ReportRepository::class),
                $worldRegionStackableUnitRepository,
                $this->createMock(WorldRegionLeveledUnitRepository::class),
                $netWorthUpdaterService,
                $gameUnitRegistry
            ),
            new ResearchProcessor(
                $this->createMock(ResearchPlayerRepository::class),
                $this->createMock(ReportRepository::class),
                $netWorthUpdaterService,
                new ResearchRegistry()
            ),
            $constructionRepository,
            $this->createMock(ResearchPlayerRepository::class),
            $this->createMock(PlayerRepository::class),
            $this->createIncomeUpdaterService($worldRegionStackableUnitRepository),
            new ResourceAccrualCalculator(),
            $entityManager ?? $this->createMock(EntityManagerInterface::class),
            $entityLockRepository,
            0,
            0
        );
    }

    protected function createIncomeUpdaterService(
        ?WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository = null
    ): IncomeUpdaterService {
        $gameUnitRegistry = new GameUnitRegistry();
        $worldRegionStackableUnitRepository ??= $this->createMock(WorldRegionStackableUnitRepository::class);
        $worldRegionLeveledUnitRepository = $this->createMock(WorldRegionLeveledUnitRepository::class);
        $fleetUnitRepository = $this->createMock(FleetUnitRepository::class);

        return new IncomeUpdaterService(
            $this->createMock(PlayerRepository::class),
            new IncomeCalculator(
                $gameUnitRegistry,
                $worldRegionStackableUnitRepository,
                $worldRegionLeveledUnitRepository,
                $fleetUnitRepository
            ),
            new UpkeepCalculator(
                $gameUnitRegistry,
                $worldRegionStackableUnitRepository,
                $worldRegionLeveledUnitRepository,
                $fleetUnitRepository
            )
        );
    }

    protected function createNetWorthUpdaterService(): NetWorthUpdaterService
    {
        return new NetWorthUpdaterService(
            $this->createMock(FederationRepository::class),
            $this->createMock(PlayerRepository::class),
            new NetWorthCalculator(
                $this->createMock(WorldRegionStackableUnitRepository::class),
                $this->createMock(WorldRegionLeveledUnitRepository::class)
            )
        );
    }
}
