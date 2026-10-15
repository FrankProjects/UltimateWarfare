<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use FrankProjects\UltimateWarfare\Service\PopulationUpdaterService;
use PHPUnit\Framework\TestCase;

class PlayerStateUpdaterServiceTest extends TestCase
{
    use CreatesPlayerStateUpdaterService;

    private const int START = 1_700_000_000;
    private const int FIRST_TICK = 1_700_000_280;

    /**
     * @var Construction[]
     */
    private array $queue = [];

    public function testAppliesNewRateFromCompletionTimeOnly(): void
    {
        [$playerStateUpdaterService, $player] = $this->createScenario();

        // Farms complete one hour after START; update one hour after that
        $playerStateUpdaterService->updateStateForPlayer($player, self::START + 7200);

        self::assertSame(1000 + $this->getFarmFoodIncome(), $player->getResources()->getFood());
        self::assertSame(self::START + 7200, $player->getTimestampUpdate());
    }

    public function testResultIsIndependentOfWhenAndHowOftenUpdatesRun(): void
    {
        [$onceEngine, $once] = $this->createScenario();
        $onceEngine->updateStateForPlayer($once, self::START + 7 * 86400);

        [$oftenEngine, $often] = $this->createScenario();
        foreach ([600, 3599, 3601, 3700, 5000, 86400, 7 * 86400] as $offset) {
            $oftenEngine->updateStateForPlayer($often, self::START + $offset);
        }

        self::assertEquals($once->getResources(), $often->getResources());
        self::assertSame(1000 + 167 * $this->getFarmFoodIncome(), $once->getResources()->getFood());
    }

    public function testPopulationGrowsByOnePerHouseEveryTickUpToCapacity(): void
    {
        [$playerStateUpdaterService, $player, $worldRegion] = $this->createPopulationScenario();

        // 2 Houses for the first hour (10 ticks), then 5 Houses
        $playerStateUpdaterService->updateStateForPlayer($player, self::START + 7200);
        self::assertSame(10 * 2 + 10 * 5, $worldRegion->getPopulation());
        self::assertSame(7, $player->getIncome()->getCash());

        $playerStateUpdaterService->updateStateForPlayer($player, self::START + 7 * 86400);
        self::assertSame(5 * PopulationUpdaterService::POPULATION_PER_HOUSE, $worldRegion->getPopulation());
        self::assertSame(250, $player->getIncome()->getCash());
    }

    public function testPopulationResultIsIndependentOfWhenAndHowOftenUpdatesRun(): void
    {
        [$onceEngine, $once, $onceRegion] = $this->createPopulationScenario();
        $onceEngine->updateStateForPlayer($once, self::START + 7 * 86400);

        [$oftenEngine, $often, $oftenRegion] = $this->createPopulationScenario();
        $firstTick = intdiv(self::START, 360) * 360 + 360;
        foreach ([1, 359, $firstTick - self::START, 3600, 3601, 5000, 86400, 7 * 86400] as $offset) {
            $oftenEngine->updateStateForPlayer($often, self::START + $offset);
        }

        self::assertSame($onceRegion->getPopulation(), $oftenRegion->getPopulation());
        self::assertEquals($once->getResources(), $often->getResources());
        self::assertGreaterThan(0, $once->getResources()->getCash());
    }

    public function testStarvingLosesPopulationEveryTick(): void
    {
        [$playerStateUpdaterService, $player, $worldRegion] = $this->createStarvationScenario();

        // Food runs out halfway the first tick
        $playerStateUpdaterService->updateStateForPlayer($player, self::START + 3600);

        $expectedPopulation = 10_000;
        for ($tick = 0; $tick < 9; $tick++) {
            $expectedPopulation -= (int) ceil($expectedPopulation * 0.03505);
        }
        self::assertSame($expectedPopulation, $worldRegion->getPopulation());
        self::assertSame(intdiv($expectedPopulation, 10), $player->getIncome()->getCash());
        self::assertSame(0, $player->getResources()->getFood());
    }

    public function testStarvingEmptiesThePopulationButKeepsTheUnits(): void
    {
        [$playerStateUpdaterService, $player, $worldRegion] = $this->createStarvationScenario();

        $playerStateUpdaterService->updateStateForPlayer($player, self::START + 30 * 86400);

        self::assertSame(0, $worldRegion->getPopulation());
        self::assertSame(1_000, $this->getSoldiers($worldRegion));
        self::assertSame(1_000, $player->getUpkeep()->getFood());
    }

    public function testStarvationResultIsIndependentOfWhenAndHowOftenUpdatesRun(): void
    {
        [$onceEngine, $once, $onceRegion] = $this->createStarvationScenario();
        $onceEngine->updateStateForPlayer($once, self::START + 86400);

        [$oftenEngine, $often, $oftenRegion] = $this->createStarvationScenario();
        foreach ([1, 100, 359, 361, 3600, 3601, 5000, 86400] as $offset) {
            $oftenEngine->updateStateForPlayer($often, self::START + $offset);
        }

        self::assertSame($onceRegion->getPopulation(), $oftenRegion->getPopulation());
        self::assertSame($this->getSoldiers($onceRegion), $this->getSoldiers($oftenRegion));
        self::assertEquals($once->getResources(), $often->getResources());
    }

    public function testNextCompletionIsWhenStarvationStarts(): void
    {
        [$playerStateUpdaterService, $player] = $this->createStarvationScenario();

        self::assertSame(
            self::FIRST_TICK + 360,
            $playerStateUpdaterService->getNextCompletionTimestamp($player)
        );
    }

    public function testUpdateDuePlayersUpdatesPlayersWithPopulationGrowthDue(): void
    {
        $player = new Player();
        $player->setId(1);
        $player->setTimestampUpdate(self::START);

        $playerRepository = $this->createMock(PlayerRepository::class);
        $playerRepository->method('findWithPopulationBelowCapacityUpdatedBefore')->willReturn([$player]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('refresh')->with($player, LockMode::PESSIMISTIC_WRITE);

        $this->createPlayerStateUpdaterService(null, null, $entityManager, null, null, $playerRepository)
            ->updateDuePlayers(time());
    }

    public function testUpdateDuePlayersOnlyUpdatesPlayersWithDueQueueItems(): void
    {
        [, $player] = $this->createScenario();

        $constructionRepository = $this->createMock(ConstructionRepository::class);
        $constructionRepository->method('getCompletedConstructions')->willReturn($this->queue);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('refresh')->with($player, LockMode::PESSIMISTIC_WRITE);

        $this->createPlayerStateUpdaterService($constructionRepository, null, $entityManager)->updateDuePlayers(time());
    }

    public function testNextCompletionIsTheEarliestOfConstructionAndResearch(): void
    {
        $player = new Player();
        $constructionRepository = $this->createMock(ConstructionRepository::class);
        $constructionRepository->method('getNextCompletionTimestampByPlayer')->willReturn(2000);
        $researchPlayerRepository = $this->createMock(ResearchPlayerRepository::class);
        $researchPlayerRepository->method('getNextCompletionTimestampByPlayer')->willReturn(1500);

        $service = $this->createPlayerStateUpdaterService(
            $constructionRepository,
            null,
            null,
            $researchPlayerRepository
        );
        self::assertSame(1500, $service->getNextCompletionTimestamp($player));

        self::assertNull($this->createPlayerStateUpdaterService()->getNextCompletionTimestamp($player));
    }

    /**
     * Player without units who queued 10 farms at START that complete after one hour
     *
     * @return array{PlayerStateUpdaterService, Player}
     */
    private function createScenario(): array
    {
        $player = new Player();
        $player->setId(1);
        $player->setTimestampUpdate(self::START);
        $player->getResources()->setFood(1000);

        $worldRegion = new WorldRegion();
        $worldRegion->setPlayer($player);
        $player->getWorldRegions()->add($worldRegion);

        $construction = Construction::create($worldRegion, $player, GameUnitEnum::FARM, 10, 3600);
        $construction->setCompletionTimestamp(self::START + 3600);
        $this->queue = [$construction];

        return [$this->createScenarioService(), $player];
    }

    /**
     * Player with an empty region with 2 Houses at START, who queued 3 more Houses that complete after one hour
     *
     * @return array{PlayerStateUpdaterService, Player, WorldRegion}
     */
    private function createPopulationScenario(): array
    {
        $player = new Player();
        $player->setId(1);
        $player->setTimestampUpdate(self::START);

        $worldRegion = new WorldRegion();
        $worldRegion->setPlayer($player);
        $player->getWorldRegions()->add($worldRegion);
        WorldRegionStackableUnit::create($worldRegion, GameUnitEnum::HOUSE, 2);

        $construction = Construction::create($worldRegion, $player, GameUnitEnum::HOUSE, 3, 3600);
        $construction->setCompletionTimestamp(self::START + 3600);
        $this->queue = [$construction];

        return [$this->createScenarioService(), $player, $worldRegion];
    }

    /**
     * Player at the first tick after START with a region of 10.000 people and 1.000 Soldiers, whose food runs out
     * halfway the next tick
     *
     * @return array{PlayerStateUpdaterService, Player, WorldRegion}
     */
    private function createStarvationScenario(): array
    {
        $player = new Player();
        $player->setId(1);
        $player->setTimestampUpdate(self::FIRST_TICK);
        $player->getResources()->setFood(50);

        $worldRegion = new WorldRegion();
        $worldRegion->setPlayer($player);
        $worldRegion->setPopulation(10_000);
        $player->getWorldRegions()->add($worldRegion);
        WorldRegionStackableUnit::create($worldRegion, GameUnitEnum::SOLDIER, 1_000);
        $this->queue = [];

        $playerStateUpdaterService = $this->createScenarioService();
        $player->getUpkeep()->setFood(1_000);

        return [$playerStateUpdaterService, $player, $worldRegion];
    }

    private function getSoldiers(WorldRegion $worldRegion): int
    {
        foreach ($worldRegion->getWorldRegionStackableUnits() as $unit) {
            if ($unit->getGameUnit() === GameUnitEnum::SOLDIER) {
                return $unit->getAmount();
            }
        }

        return 0;
    }

    private function createScenarioService(): PlayerStateUpdaterService
    {
        $constructionRepository = $this->createMock(ConstructionRepository::class);
        $constructionRepository->method('getCompletedConstructionsByPlayer')->willReturnCallback(
            fn(Player $player, int $timestamp): array => array_values(array_filter(
                $this->queue,
                fn(Construction $c): bool => $c->getCompletionTimestamp() < $timestamp
            ))
        );
        $constructionRepository->method('remove')->willReturnCallback(
            function (Construction $construction): void {
                $this->queue = array_values(array_filter($this->queue, fn($c) => $c !== $construction));
            }
        );

        // Stands in for the database aggregate: sums the units in the player's regions
        $worldRegionStackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $worldRegionStackableUnitRepository->method('getAmountSumByPlayer')->willReturnCallback(
            function (Player $player): array {
                $totals = [];
                foreach ($player->getWorldRegions() as $worldRegion) {
                    foreach ($worldRegion->getWorldRegionStackableUnits() as $unit) {
                        $id = $unit->getGameUnit()->value;
                        $totals[$id] = ($totals[$id] ?? 0) + $unit->getAmount();
                    }
                }

                return $totals;
            }
        );

        // Stands in for the database queries on the population of the player's regions
        $worldRegionRepository = $this->createMock(WorldRegionRepository::class);
        $worldRegionRepository->method('findBelowPopulationCapacityByPlayer')->willReturnCallback(
            function (Player $player, int $populationPerHouse): array {
                $belowCapacity = [];
                foreach ($player->getWorldRegions() as $worldRegion) {
                    foreach ($worldRegion->getWorldRegionStackableUnits() as $unit) {
                        $houses = $unit->getAmount();
                        if (
                            $unit->getGameUnit() === GameUnitEnum::HOUSE
                            && $worldRegion->getPopulation() < $houses * $populationPerHouse
                        ) {
                            $belowCapacity[] = [$worldRegion, $houses];
                        }
                    }
                }

                return $belowCapacity;
            }
        );
        $worldRegionRepository->method('findWithPopulationByPlayer')->willReturnCallback(
            fn(Player $player): array => array_values(array_filter(
                $player->getWorldRegions()->toArray(),
                fn(WorldRegion $worldRegion): bool => $worldRegion->getPopulation() > 0
            ))
        );
        $worldRegionRepository->method('getPopulationSumByPlayer')->willReturnCallback(
            fn(Player $player): int => array_sum(array_map(
                fn(WorldRegion $worldRegion): int => $worldRegion->getPopulation(),
                $player->getWorldRegions()->toArray()
            ))
        );

        return $this->createPlayerStateUpdaterService(
            $constructionRepository,
            $worldRegionStackableUnitRepository,
            null,
            null,
            $worldRegionRepository
        );
    }

    /**
     * Net food income per hour of 10 farms
     */
    private function getFarmFoodIncome(): int
    {
        $farm = (new GameUnitRegistry())->find(GameUnitEnum::FARM);

        return 10 * ($farm->getIncome()->getFood() - $farm->getUpkeep()->getFood());
    }
}
