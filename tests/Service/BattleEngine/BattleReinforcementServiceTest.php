<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleReinforcementService;
use FrankProjects\UltimateWarfare\Service\BattleEngine\Reinforcement;
use PHPUnit\Framework\TestCase;

class BattleReinforcementServiceTest extends TestCase
{
    private Player $defender;
    private WorldRegion $targetRegion;

    protected function setUp(): void
    {
        $this->defender = new Player();
        $this->targetRegion = $this->createRegion($this->defender, 5, 5);
    }

    public function testTrainStationSendsSoldiersAndTanksPerLevel(): void
    {
        $neighbour = $this->createRegion($this->defender, 6, 5);
        WorldRegionLeveledUnit::create($neighbour, GameUnitEnum::TRAIN_STATION, 2, 12000);
        $soldiers = WorldRegionStackableUnit::create($neighbour, GameUnitEnum::SOLDIER, 100);
        WorldRegionStackableUnit::create($neighbour, GameUnitEnum::TANK, 1);

        $service = $this->createService([$neighbour]);
        $reinforcements = $service->gatherReinforcements($this->targetRegion, $this->defender);

        // Level 2 sends up to 50 soldiers and 2 tanks; only 1 tank is available
        self::assertSame(
            [[GameUnitEnum::SOLDIER, 50], [GameUnitEnum::TANK, 1]],
            $this->summarize($reinforcements)
        );
        self::assertSame($soldiers, $reinforcements[0]->getSourceUnit());
        self::assertNotSame($soldiers, $reinforcements[0]->getBattleUnit());
        self::assertNotContains($reinforcements[0]->getBattleUnit(), $neighbour->getWorldRegionStackableUnits());
        self::assertSame(
            ['Reinforcements arrive by train from region 6, 5: 50 Soldiers, 1 Tank'],
            $service->getBattleLog($reinforcements)
        );
    }

    public function testAirfieldSendsFightersFirst(): void
    {
        $neighbour = $this->createRegion($this->defender, 4, 5);
        WorldRegionLeveledUnit::create($neighbour, GameUnitEnum::AIRFIELD, 1, 7000);
        WorldRegionStackableUnit::create($neighbour, GameUnitEnum::BOMBER, 20);
        WorldRegionStackableUnit::create($neighbour, GameUnitEnum::FIGHTER, 6);

        $reinforcements = $this->createService([$neighbour])
            ->gatherReinforcements($this->targetRegion, $this->defender);

        self::assertSame(
            [[GameUnitEnum::FIGHTER, 6], [GameUnitEnum::BOMBER, 4]],
            $this->summarize($reinforcements)
        );
    }

    public function testHarborSendsToughestShipsFirst(): void
    {
        $neighbour = $this->createRegion($this->defender, 4, 5);
        WorldRegionLeveledUnit::create($neighbour, GameUnitEnum::HARBOR, 2, 14000);
        WorldRegionStackableUnit::create($neighbour, GameUnitEnum::PATROL_BOAT, 5);
        WorldRegionStackableUnit::create($neighbour, GameUnitEnum::CRUISER, 1);

        $service = $this->createService([$neighbour]);
        $reinforcements = $service->gatherReinforcements($this->targetRegion, $this->defender);

        self::assertSame(
            [[GameUnitEnum::CRUISER, 1], [GameUnitEnum::PATROL_BOAT, 1]],
            $this->summarize($reinforcements)
        );
        self::assertSame(
            ['Reinforcements sail in from region 4, 5: 1 Cruiser, 1 Patrol boat'],
            $service->getBattleLog($reinforcements)
        );
    }

    public function testRegionsOfOtherPlayersAndRegionsWithoutBuildingsSendNothing(): void
    {
        $otherPlayersRegion = $this->createRegion(new Player(), 6, 5);
        WorldRegionLeveledUnit::create($otherPlayersRegion, GameUnitEnum::TRAIN_STATION, 1, 6000);
        WorldRegionStackableUnit::create($otherPlayersRegion, GameUnitEnum::SOLDIER, 100);

        $regionWithoutBuildings = $this->createRegion($this->defender, 4, 5);
        WorldRegionStackableUnit::create($regionWithoutBuildings, GameUnitEnum::SOLDIER, 100);

        $reinforcements = $this->createService([$otherPlayersRegion, $regionWithoutBuildings])
            ->gatherReinforcements($this->targetRegion, $this->defender);

        self::assertSame([], $reinforcements);
    }

    public function testApplyLossesTakesDeadReinforcementsFromTheirHomeRegion(): void
    {
        $neighbour = $this->createRegion($this->defender, 6, 5);
        WorldRegionLeveledUnit::create($neighbour, GameUnitEnum::TRAIN_STATION, 1, 6000);
        $soldiers = WorldRegionStackableUnit::create($neighbour, GameUnitEnum::SOLDIER, 100);
        $tanks = WorldRegionStackableUnit::create($neighbour, GameUnitEnum::TANK, 1);

        $stackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $stackableUnitRepository->expects(self::once())->method('save')->with($soldiers);
        $stackableUnitRepository->expects(self::once())->method('remove')->with($tanks);

        $service = $this->createService([$neighbour], $stackableUnitRepository);
        [$soldierReinforcement, $tankReinforcement] = $service->gatherReinforcements(
            $this->targetRegion,
            $this->defender
        );

        // 10 of the 25 soldiers survive the battle, the only tank dies
        $soldierReinforcement->getBattleUnit()->setAmount(10);
        $service->applyLosses([$soldierReinforcement, $tankReinforcement], [$soldierReinforcement->getBattleUnit()]);

        self::assertSame(85, $soldiers->getAmount());
        self::assertNotContains($tanks, $neighbour->getWorldRegionStackableUnits());
    }

    public function testApplyLossesLeavesSurvivorsUntouched(): void
    {
        $neighbour = $this->createRegion($this->defender, 6, 5);
        WorldRegionLeveledUnit::create($neighbour, GameUnitEnum::TRAIN_STATION, 1, 6000);
        $soldiers = WorldRegionStackableUnit::create($neighbour, GameUnitEnum::SOLDIER, 100);

        $stackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $stackableUnitRepository->expects(self::never())->method('save');
        $stackableUnitRepository->expects(self::never())->method('remove');

        $service = $this->createService([$neighbour], $stackableUnitRepository);
        $reinforcements = $service->gatherReinforcements($this->targetRegion, $this->defender);
        $service->applyLosses($reinforcements, [$reinforcements[0]->getBattleUnit()]);

        self::assertSame(100, $soldiers->getAmount());
    }

    /**
     * @param WorldRegion[] $adjacentRegions
     */
    private function createService(
        array $adjacentRegions,
        ?WorldRegionStackableUnitRepository $stackableUnitRepository = null
    ): BattleReinforcementService {
        $worldRegionRepository = $this->createMock(WorldRegionRepository::class);
        $worldRegionRepository->method('findAdjacentRegions')->willReturn($adjacentRegions);

        return new BattleReinforcementService(
            $worldRegionRepository,
            $stackableUnitRepository ?? $this->createMock(WorldRegionStackableUnitRepository::class),
            new GameUnitRegistry()
        );
    }

    private function createRegion(Player $player, int $x, int $y): WorldRegion
    {
        $worldRegion = new WorldRegion();
        $worldRegion->setWorld(new World());
        $worldRegion->setX($x);
        $worldRegion->setY($y);
        $worldRegion->setPlayer($player);

        return $worldRegion;
    }

    /**
     * @param Reinforcement[] $reinforcements
     *
     * @return array<array{GameUnitEnum, int}>
     */
    private function summarize(array $reinforcements): array
    {
        return array_map(
            fn (Reinforcement $reinforcement): array => [
                $reinforcement->getBattleUnit()->getGameUnit(),
                $reinforcement->getAmount(),
            ],
            $reinforcements
        );
    }
}
