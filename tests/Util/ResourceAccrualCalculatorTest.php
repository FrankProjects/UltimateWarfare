<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;
use PHPUnit\Framework\TestCase;

class ResourceAccrualCalculatorTest extends TestCase
{
    private const int START = 1_700_000_000;

    private ResourceAccrualCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new ResourceAccrualCalculator();
    }

    public function testAccruesHourlyRate(): void
    {
        $player = $this->createPlayer(cashIncome: 3600, cashUpkeep: 600);

        $this->calculator->accrue($player, self::START + 7200);

        self::assertSame(1000 + 6000, $player->getResources()->getCash());
        self::assertSame(self::START + 7200, $player->getTimestampUpdate());
    }

    public function testDoesNothingWhenNotAfterLastUpdate(): void
    {
        $player = $this->createPlayer(cashIncome: 3600);

        $this->calculator->accrue($player, self::START);
        $this->calculator->accrue($player, self::START - 100);

        self::assertSame(1000, $player->getResources()->getCash());
        self::assertSame(self::START, $player->getTimestampUpdate());
    }

    public function testFrequentAccrualEqualsSingleAccrual(): void
    {
        $once = $this->createPlayer(cashIncome: 1234, foodIncome: 77, woodIncome: 5, steelIncome: 999);
        $often = $this->createPlayer(cashIncome: 1234, foodIncome: 77, woodIncome: 5, steelIncome: 999);
        $end = self::START + 7 * 86400 + 13;

        $this->calculator->accrue($once, $end);

        mt_srand(42);
        $timestamp = self::START;
        while ($timestamp < $end) {
            $timestamp = min($end, $timestamp + mt_rand(1, 400));
            $this->calculator->accrue($often, $timestamp);
        }

        self::assertEquals($once->getResources(), $often->getResources());
    }

    public function testLowRateIsNotLostToRounding(): void
    {
        // 100 per hour is 0.028 per second: truncating every 10 seconds would never gain anything
        $player = $this->createPlayer(cashIncome: 100);

        for ($i = 1; $i <= 360; $i++) {
            $this->calculator->accrue($player, self::START + $i * 10);
        }

        self::assertSame(1000 + 100, $player->getResources()->getCash());
    }

    public function testNegativeRateIsExactAndClampsAtZero(): void
    {
        $player = $this->createPlayer(cashUpkeep: 100, foodUpkeep: 7200);

        for ($i = 1; $i <= 360; $i++) {
            $this->calculator->accrue($player, self::START + $i * 10);
        }

        // 1 hour of upkeep: cash goes 1000 -> 900, food (2 per second) hits zero after 500 seconds and stays there
        self::assertSame(900, $player->getResources()->getCash());
        self::assertSame(0, $player->getResources()->getFood());
    }

    public function testProjectMatchesAccrueWithoutChangingThePlayer(): void
    {
        $projected = $this->createPlayer(cashIncome: 1234, foodUpkeep: 50000, woodIncome: 5);
        $accrued = $this->createPlayer(cashIncome: 1234, foodUpkeep: 50000, woodIncome: 5);
        $at = self::START + 86400 + 17;

        $resources = $this->calculator->project($projected, $at);
        $this->calculator->accrue($accrued, $at);

        self::assertEquals($accrued->getResources(), $resources);
        self::assertSame(1000, $projected->getResources()->getCash());
        self::assertSame(self::START, $projected->getTimestampUpdate());
    }

    public function testProjectBeforeLastUpdateReturnsACopyOfStoredResources(): void
    {
        $player = $this->createPlayer(cashIncome: 3600);

        $resources = $this->calculator->project($player, self::START - 100);
        $resources->setCash(5);

        self::assertSame(1000, $player->getResources()->getCash());
    }

    private function createPlayer(
        int $cashIncome = 0,
        int $foodIncome = 0,
        int $woodIncome = 0,
        int $steelIncome = 0,
        int $cashUpkeep = 0,
        int $foodUpkeep = 0
    ): Player {
        $player = new Player();
        $player->setTimestampUpdate(self::START);
        $player->getResources()->setCash(1000);
        $player->getResources()->setFood(1000);
        $player->getResources()->setWood(1000);
        $player->getResources()->setSteel(1000);
        $player->getIncome()->setCash($cashIncome);
        $player->getIncome()->setFood($foodIncome);
        $player->getIncome()->setWood($woodIncome);
        $player->getIncome()->setSteel($steelIncome);
        $player->getUpkeep()->setCash($cashUpkeep);
        $player->getUpkeep()->setFood($foodUpkeep);

        return $player;
    }
}
