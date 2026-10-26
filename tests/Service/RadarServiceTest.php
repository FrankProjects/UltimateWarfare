<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Service\RadarService;
use PHPUnit\Framework\TestCase;

class RadarServiceTest extends TestCase
{
    public function testCoverageIsEmptyWithoutRadarStation(): void
    {
        $player = $this->createPlayer();
        $this->createRegion($player, 5, 5);

        self::assertSame([], $this->createService([])->getCoverage($player));
    }

    public function testCoverageRadiusEqualsRadarLevel(): void
    {
        $player = $this->createPlayer();
        $region = $this->createRegion($player, 10, 10);
        WorldRegionLeveledUnit::create($region, GameUnitEnum::RADAR_STATION, 2, 6000);

        $coverage = $this->createService([])->getCoverage($player);

        // A hex disk of radius 2 holds 1 + 6 + 12 tiles
        self::assertCount(19, $coverage);
        self::assertArrayHasKey('12,10', $coverage);
        self::assertArrayNotHasKey('13,10', $coverage);
    }

    public function testDetectsFleetWhoseRouteCrossesCoverage(): void
    {
        $player = $this->createPlayer();
        $radarRegion = $this->createRegion($player, 10, 10);
        WorldRegionLeveledUnit::create($radarRegion, GameUnitEnum::RADAR_STATION, 1, 6000);

        $enemy = $this->createPlayer();
        $passing = $this->createFleet($enemy, [[9, 8], [9, 9], [9, 10], [8, 10]]);
        $farAway = $this->createFleet($enemy, [[20, 20], [21, 20]]);

        $service = $this->createService([$passing, $farAway]);

        self::assertSame([$passing], $service->findDetectedFleets($player, $service->getCoverage($player)));
    }

    public function testDetectsAircraftFlyingStraightOverCoverage(): void
    {
        $player = $this->createPlayer();
        $radarRegion = $this->createRegion($player, 10, 10);
        WorldRegionLeveledUnit::create($radarRegion, GameUnitEnum::RADAR_STATION, 1, 6000);

        // Both ends are far outside the radar range, the straight line passes over the radar region
        $aircraft = $this->createFleet($this->createPlayer(), [[4, 10], [16, 10]]);

        $service = $this->createService([$aircraft]);

        self::assertSame([$aircraft], $service->findDetectedFleets($player, $service->getCoverage($player)));
    }

    public function testIgnoresFleetsOfFederationMembers(): void
    {
        $federation = new Federation();
        $federation->setId(1);

        $player = $this->createPlayer();
        $player->setFederation($federation);
        $radarRegion = $this->createRegion($player, 10, 10);
        WorldRegionLeveledUnit::create($radarRegion, GameUnitEnum::RADAR_STATION, 1, 6000);

        $ally = $this->createPlayer();
        $ally->setFederation($federation);
        $allyFleet = $this->createFleet($ally, [[10, 9], [10, 10]]);

        $service = $this->createService([$allyFleet]);

        self::assertSame([], $service->findDetectedFleets($player, $service->getCoverage($player)));
    }

    private function createPlayer(): Player
    {
        $player = new Player();
        $player->setWorld(new World());

        return $player;
    }

    private function createRegion(?Player $player, int $x, int $y): WorldRegion
    {
        $region = new WorldRegion();
        $region->setType(WorldRegion::TYPE_GRASSLAND);
        $region->setX($x);
        $region->setY($y);
        $region->setPlayer($player);
        $player?->getWorldRegions()->add($region);

        return $region;
    }

    /**
     * @param non-empty-list<array{int, int}> $path
     */
    private function createFleet(Player $player, array $path): Fleet
    {
        $fleet = new Fleet();
        $fleet->setPlayer($player);
        $fleet->setWorldRegion($this->createRegion($player, $path[0][0], $path[0][1]));
        $fleet->setTargetWorldRegion($this->createRegion(null, $path[count($path) - 1][0], $path[count($path) - 1][1]));
        $fleet->setPath($path);

        return $fleet;
    }

    /**
     * @param list<Fleet> $fleets
     */
    private function createService(array $fleets): RadarService
    {
        $fleetRepository = self::createStub(FleetRepository::class);
        $fleetRepository->method('findByWorldExcludingPlayer')->willReturn($fleets);

        return new RadarService($fleetRepository);
    }
}
