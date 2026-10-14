<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use PHPUnit\Framework\TestCase;

class PlayerStateUpdaterServiceTest extends TestCase
{
    use CreatesPlayerStateUpdaterService;

    private const int START = 1_700_000_000;

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

        return $this->createPlayerStateUpdaterService($constructionRepository, $worldRegionStackableUnitRepository);
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
