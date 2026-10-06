<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleUpdaterService;
use PHPUnit\Framework\TestCase;

class BattleUpdaterServiceTest extends TestCase
{
    public function testBattleLostOnlyRemovesDefendersThatDied(): void
    {
        $region = new WorldRegion();
        $survivingSoldiers = WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);
        $deadTanks = WorldRegionStackableUnit::create($region, GameUnitEnum::TANK, 5);
        $survivingBarrack = WorldRegionLeveledUnit::create($region, GameUnitEnum::BARRACK, 1, 6000);
        $destroyedAirfield = WorldRegionLeveledUnit::create($region, GameUnitEnum::AIRFIELD, 1, 6000);

        $fleet = new Fleet();
        $fleet->setTargetWorldRegion($region);

        $stackableUnitRepository = $this->createMock(WorldRegionStackableUnitRepository::class);
        $stackableUnitRepository->expects(self::once())->method('remove')->with($deadTanks);
        $stackableUnitRepository->expects(self::once())->method('save')->with($survivingSoldiers);

        $leveledUnitRepository = $this->createMock(WorldRegionLeveledUnitRepository::class);
        $leveledUnitRepository->expects(self::once())->method('remove')->with($destroyedAirfield);
        $leveledUnitRepository->expects(self::once())->method('save')->with($survivingBarrack);

        $service = new BattleUpdaterService(
            $this->createMock(FleetRepository::class),
            $this->createMock(FleetUnitRepository::class),
            $this->createMock(WorldRegionRepository::class),
            $stackableUnitRepository,
            $leveledUnitRepository
        );

        $service->updateBattleLost($fleet, [], [$survivingSoldiers, $survivingBarrack]);
    }
}
