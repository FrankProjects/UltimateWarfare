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
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use FrankProjects\UltimateWarfare\Service\IncomeUpdaterService;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use FrankProjects\UltimateWarfare\Service\PlayerState\ConstructionCompleter;
use FrankProjects\UltimateWarfare\Service\PlayerState\ResearchCompleter;
use FrankProjects\UltimateWarfare\Service\PopulationUpdaterService;
use FrankProjects\UltimateWarfare\Service\StarvationUpdaterService;
use FrankProjects\UltimateWarfare\Util\IncomeCalculator;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;
use FrankProjects\UltimateWarfare\Util\UpkeepCalculator;
use PHPUnit\Framework\TestCase;

/**
 * PlayerStateUpdaterService and its collaborators are final; build real instances on top of mocked repositories.
 * The mocked EntityManager makes transactions and row locks no-ops.
 *
 * @mixin TestCase
 */
trait CreatesPlayerStateUpdaterService
{
    protected function createPlayerStateUpdaterService(
        ?ConstructionRepository $constructionRepository = null,
        ?WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository = null,
        ?EntityManagerInterface $entityManager = null,
        ?ResearchPlayerRepository $researchPlayerRepository = null,
        ?WorldRegionRepository $worldRegionRepository = null,
        ?PlayerRepository $playerRepository = null
    ): PlayerStateUpdaterService {

        $gameUnitRegistry = new GameUnitRegistry();
        $constructionRepository ??= $this->createMock(ConstructionRepository::class);
        $worldRegionStackableUnitRepository ??= $this->createMock(WorldRegionStackableUnitRepository::class);
        $netWorthUpdaterService = $this->createNetWorthUpdaterService();
        $worldRegionRepository ??= $this->createMock(WorldRegionRepository::class);
        $playerRepository ??= $this->createMock(PlayerRepository::class);
        $populationUpdaterService = new PopulationUpdaterService($worldRegionRepository, $playerRepository);

        return new PlayerStateUpdaterService(
            new ConstructionCompleter(
                $constructionRepository,
                $this->createMock(ReportRepository::class),
                $worldRegionStackableUnitRepository,
                $this->createMock(WorldRegionLeveledUnitRepository::class),
                $netWorthUpdaterService,
                $gameUnitRegistry
            ),
            new ResearchCompleter(
                $this->createMock(ResearchPlayerRepository::class),
                $this->createMock(ReportRepository::class),
                $netWorthUpdaterService,
                new ResearchRegistry()
            ),
            $constructionRepository,
            $researchPlayerRepository ?? $this->createMock(ResearchPlayerRepository::class),
            $playerRepository,
            $this->createIncomeUpdaterService($worldRegionStackableUnitRepository, $worldRegionRepository),
            $populationUpdaterService,
            new StarvationUpdaterService($worldRegionRepository, $playerRepository, $populationUpdaterService),
            new ResourceAccrualCalculator(),
            $entityManager ?? $this->createMock(EntityManagerInterface::class),
            0,
            0
        );
    }

    protected function createIncomeUpdaterService(
        ?WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository = null,
        ?WorldRegionRepository $worldRegionRepository = null
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
                $fleetUnitRepository,
                $worldRegionRepository ?? $this->createMock(WorldRegionRepository::class)
            ),
            new UpkeepCalculator(
                $gameUnitRegistry,
                $worldRegionStackableUnitRepository,
                $worldRegionLeveledUnitRepository,
                $fleetUnitRepository
            )
        );
    }

    /**
     * Every locked row still exists, unless $exists says otherwise
     */
    protected function createEntityLockRepository(bool $exists = true): EntityLockRepository
    {
        $entityLockRepository = $this->createMock(EntityLockRepository::class);
        $entityLockRepository->method('lockForUpdate')->willReturn($exists);

        return $entityLockRepository;
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
