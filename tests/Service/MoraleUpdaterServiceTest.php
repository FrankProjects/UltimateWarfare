<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\MoraleUpdaterService;
use PHPUnit\Framework\TestCase;

class MoraleUpdaterServiceTest extends TestCase
{
    // A whole hour on the Unix epoch
    private const int START = 1_699_999_200;

    private MoraleUpdaterService $service;

    protected function setUp(): void
    {
        $this->service = new MoraleUpdaterService(new GameUnitRegistry());
    }

    public function testRegionRecoversFourPerHour(): void
    {
        $region = $this->createRegion(50, self::START);

        self::assertSame(54, $this->service->project($region, self::START + 3600));
        self::assertSame(56, $this->service->project($region, self::START + 5400));
    }

    public function testRegionRecoversUpToMaxMorale(): void
    {
        $region = $this->createRegion(90, self::START);

        self::assertSame(100, $this->service->project($region, self::START + 10 * 3600));
    }

    public function testExistingRegionsStartAtMaxMorale(): void
    {
        self::assertSame(100, $this->service->project(new WorldRegion(), self::START));
    }

    public function testCheckpointingLosesNoRecovery(): void
    {
        $region = $this->createRegion(10, self::START + 123);
        $end = self::START + 5 * 3600 + 456;
        $expected = $this->service->project($region, $end);

        foreach ([self::START + 700, self::START + 2000, self::START + 9999, $end] as $at) {
            $this->service->setRegionMorale($region, $this->service->project($region, $at), $at);
        }

        self::assertSame($expected, $region->getMorale());
    }

    public function testFleetDoesNotRecover(): void
    {
        $fleet = new Fleet();
        $fleet->setMorale(40);

        self::assertSame(40, $this->service->project($fleet, self::START + 10 * 3600));
    }

    public function testStrengthMultiplier(): void
    {
        self::assertSame(0.5, $this->service->getStrengthMultiplier(0));
        self::assertSame(0.75, $this->service->getStrengthMultiplier(50));
        self::assertSame(1.0, $this->service->getStrengthMultiplier(100));
    }

    public function testCrewIsWeighedByUnitPopulation(): void
    {
        $fleet = new Fleet();

        self::assertSame(
            10 * 1 + 2 * 4,
            $this->service->getCrew([
                FleetUnit::createForFleet($fleet, GameUnitEnum::SOLDIER, 10),
                FleetUnit::createForFleet($fleet, GameUnitEnum::TANK, 2),
            ])
        );
    }

    public function testMergeAveragesByCrew(): void
    {
        $region = $this->createRegion(100, self::START);
        WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);

        $this->service->mergeIntoRegion($region, 40, 30, self::START);

        self::assertSame(55, $region->getMorale());
        self::assertSame(self::START, $region->getMoraleTimestamp());
    }

    public function testMergeIntoEmptyRegionTakesIncomingMorale(): void
    {
        $region = $this->createRegion(100, self::START);

        $this->service->mergeIntoRegion($region, 40, 30, self::START);

        self::assertSame(40, $region->getMorale());
    }

    public function testMergeUsesRecoveredRegionMorale(): void
    {
        $region = $this->createRegion(50, self::START);
        WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);

        $this->service->mergeIntoRegion($region, 58, 10, self::START + 2 * 3600);

        self::assertSame(58, $region->getMorale());
    }

    public function testAttackerPaysPerBattleAndForCasualties(): void
    {
        $fleet = new Fleet();

        $this->service->applyAttackerLoss($fleet, 100, 25, true);

        self::assertSame(100 - 10 - 10, $fleet->getMorale());
    }

    public function testAttackerPaysMoreForDefeat(): void
    {
        $fleet = new Fleet();

        $this->service->applyAttackerLoss($fleet, 100, 100, false);

        self::assertSame(100 - 10 - 40 - 10, $fleet->getMorale());
    }

    public function testMoraleDoesNotDropBelowZero(): void
    {
        $fleet = new Fleet();
        $fleet->setMorale(20);

        $this->service->applyAttackerLoss($fleet, 100, 100, false);

        self::assertSame(0, $fleet->getMorale());
    }

    public function testDefenderWithoutCasualtiesKeepsMorale(): void
    {
        $region = $this->createRegion(80, self::START);

        $this->service->applyDefenderLoss($region, 100, 0, self::START + 3600);

        self::assertSame(80, $region->getMorale());
        self::assertSame(self::START, $region->getMoraleTimestamp());
    }

    public function testDefenderLosesMoraleForCasualties(): void
    {
        $region = $this->createRegion(80, self::START);

        $this->service->applyDefenderLoss($region, 100, 50, self::START + 3600);

        self::assertSame(80 + 4 - 15, $region->getMorale());
        self::assertSame(self::START + 3600, $region->getMoraleTimestamp());
    }

    private function createRegion(int $morale, int $moraleTimestamp): WorldRegion
    {
        $region = new WorldRegion();
        $region->setMorale($morale);
        $region->setMoraleTimestamp($moraleTimestamp);

        return $region;
    }
}
