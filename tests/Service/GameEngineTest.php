<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GameEngineTest extends TestCase
{
    use CreatesGameEngine;

    private const int START = 1_700_000_000;

    /**
     * @var Construction[]
     */
    private array $queue = [];

    public function testAppliesNewRateFromCompletionTimeOnly(): void
    {
        [$gameEngine, $player] = $this->createScenario();

        // Farms complete one hour after START; sync one hour after that
        $gameEngine->syncPlayer($player, self::START + 7200);

        self::assertSame(1000 + $this->getFarmFoodIncome(), $player->getResources()->getFood());
        self::assertSame(self::START + 7200, $player->getTimestampUpdate());
    }

    public function testResultIsIndependentOfWhenAndHowOftenSyncRuns(): void
    {
        [$onceEngine, $once] = $this->createScenario();
        $onceEngine->syncPlayer($once, self::START + 7 * 86400);

        [$oftenEngine, $often] = $this->createScenario();
        foreach ([600, 3599, 3601, 3700, 5000, 86400, 7 * 86400] as $offset) {
            $oftenEngine->syncPlayer($often, self::START + $offset);
        }

        self::assertEquals($once->getResources(), $often->getResources());
        self::assertSame(1000 + 167 * $this->getFarmFoodIncome(), $once->getResources()->getFood());
    }

    public function testTransactionalLocksPlayersInIdOrderBeforeOtherEntitiesAndCommits(): void
    {
        $first = $this->createPlayer(1);
        $second = $this->createPlayer(2);
        $federation = new Federation();

        $locked = [];
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('refresh')->willReturnCallback(
            function (object $entity, LockMode $lockMode) use (&$locked): void {
                self::assertSame(LockMode::PESSIMISTIC_WRITE, $lockMode);
                $locked[] = $entity;
            }
        );
        // One outer transaction plus a nested one per synced player
        $entityManager->expects(self::exactly(3))->method('beginTransaction');
        $entityManager->expects(self::exactly(3))->method('commit');
        $entityManager->expects(self::never())->method('rollback');

        $entityLockRepository = $this->createMock(EntityLockRepository::class);
        $entityLockRepository->method('lockForUpdate')->willReturnCallback(
            function (object $entity) use (&$locked): bool {
                $locked[] = $entity;
                return true;
            }
        );

        $gameEngine = $this->createGameEngine(null, null, $entityManager, $entityLockRepository);
        $result = $gameEngine->transactional(
            [$second, $first, $second],
            fn(): bool => $gameEngine->lockForUpdate($federation)
        );

        self::assertTrue($result);
        self::assertSame([$first, $second, $federation], $locked);
    }

    public function testTransactionalRollsBackWhenActionFails(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('rollback');
        $entityManager->expects(self::once())->method('commit'); // only the nested sync

        $this->expectException(RuntimeException::class);
        $this->createGameEngine(null, null, $entityManager)->transactional(
            [$this->createPlayer(1)],
            function (): void {
                throw new RuntimeException('You do not have enough money!');
            }
        );
    }

    public function testRunSkipsCurrentPlayerWithoutDueItemsWhenSyncedRecently(): void
    {
        $player = $this->createPlayer(1);
        $player->setTimestampUpdate(time() - 10);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('refresh');

        $this->createGameEngine(null, null, $entityManager)->run($player);
    }

    public function testRunSyncsCurrentPlayerAfterInterval(): void
    {
        $player = $this->createPlayer(1);
        $player->setTimestampUpdate(time() - 120);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('refresh')->with($player, LockMode::PESSIMISTIC_WRITE);

        $this->createGameEngine(null, null, $entityManager)->run($player);

        self::assertGreaterThanOrEqual(time() - 1, $player->getTimestampUpdate());
    }

    private function createPlayer(int $id): Player
    {
        $player = new Player();
        $player->setId($id);
        $player->setTimestampUpdate(self::START);

        return $player;
    }

    /**
     * Player without units who queued 10 farms at START that complete after one hour
     *
     * @return array{GameEngine, Player}
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
        $construction->setTimestamp(self::START);
        $this->queue = [$construction];

        return [$this->createScenarioGameEngine(), $player];
    }

    private function createScenarioGameEngine(): GameEngine
    {
        $constructionRepository = $this->createMock(ConstructionRepository::class);
        $constructionRepository->method('getCompletedConstructionsByPlayer')->willReturnCallback(
            fn(Player $player, int $timestamp): array => array_values(array_filter(
                $this->queue,
                fn(Construction $c): bool => $c->getTimestamp() + $c->getDuration() < $timestamp
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

        return $this->createGameEngine($constructionRepository, $worldRegionStackableUnitRepository);
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
