<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Util;

use FrankProjects\UltimateWarfare\Util\TimeCalculator;
use PHPUnit\Framework\TestCase;

class TimeCalculatorTest extends TestCase
{
    private TimeCalculator $timeCalculator;

    protected function setUp(): void
    {
        $this->timeCalculator = new TimeCalculator();
    }

    public function testZeroSeconds(): void
    {
        self::assertEquals('00:00:00', $this->timeCalculator->calculateTimeLeft(0));
    }

    public function testSecondsOnly(): void
    {
        self::assertEquals('00:00:30', $this->timeCalculator->calculateTimeLeft(30));
    }

    public function testSecondsMaxBoundary(): void
    {
        self::assertEquals('00:00:59', $this->timeCalculator->calculateTimeLeft(59));
    }

    public function testMinutesAndSeconds(): void
    {
        self::assertEquals('00:01:01', $this->timeCalculator->calculateTimeLeft(61));
    }

    public function testMinutesOnly(): void
    {
        self::assertEquals('00:05:00', $this->timeCalculator->calculateTimeLeft(300));
    }

    public function testMinutesMaxBoundary(): void
    {
        self::assertEquals('00:59:59', $this->timeCalculator->calculateTimeLeft(3599));
    }

    public function testOneHourExactly(): void
    {
        self::assertEquals('1:00:00', $this->timeCalculator->calculateTimeLeft(3600));
    }

    public function testHoursMinutesAndSeconds(): void
    {
        self::assertEquals('1:01:01', $this->timeCalculator->calculateTimeLeft(3661));
    }

    public function testMultipleHours(): void
    {
        self::assertEquals('10:00:00', $this->timeCalculator->calculateTimeLeft(36000));
    }

    public function testLargeValue(): void
    {
        self::assertEquals('100:00:00', $this->timeCalculator->calculateTimeLeft(360000));
    }

    public function testPadsMinutesAndSeconds(): void
    {
        self::assertEquals('2:03:05', $this->timeCalculator->calculateTimeLeft(7385));
    }
}
