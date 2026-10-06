<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Util;

use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use PHPUnit\Framework\TestCase;

class DistanceCalculatorTest extends TestCase
{
    public function testCalculateDistance(): void
    {
        $calculator = new DistanceCalculator(0);
        $result = $calculator->calculateDistance(1, 1, 1, 1);
        self::assertEquals(0, $result);

        $result = $calculator->calculateDistance(1, 1, 2, 1);
        self::assertEquals(2, $result);

        $result = $calculator->calculateDistance(1, 1, 10, 10);
        self::assertEquals(26, $result);
    }

    public function testCalculateDistanceTravelTime(): void
    {
        $calculator = new DistanceCalculator(0);

        $result = $calculator->calculateDistanceTravelTime(1, 1, 1, 1);
        self::assertEquals(0, $result); // 0 distance * 100

        $result = $calculator->calculateDistanceTravelTime(1, 1, 2, 1);
        self::assertEquals(200, $result); // 2 distance * 100

        $result = $calculator->calculateDistanceTravelTime(1, 1, 10, 10);
        self::assertEquals(2600, $result); // 26 distance * 100
    }

    public function testCalculateDistanceTravelTimeWithOverride(): void
    {
        $calculator = new DistanceCalculator(1);

        // When override is set, travel time should always be 1
        $result = $calculator->calculateDistanceTravelTime(1, 1, 1, 1);
        self::assertEquals(1, $result);

        $result = $calculator->calculateDistanceTravelTime(1, 1, 10, 10);
        self::assertEquals(1, $result);

        $result = $calculator->calculateDistanceTravelTime(1, 1, 100, 100);
        self::assertEquals(1, $result);
    }

    public function testCalculateFleetTravelTimeAppliesTrainStationBonus(): void
    {
        $calculator = new DistanceCalculator(0);

        // distance(1,1,10,10) * 100 = 2600 base travel time.
        // Level 0 and 1 give no bonus (level 1 only unlocks the building).
        self::assertEquals(2600, $calculator->calculateFleetTravelTime(1, 1, 10, 10, 0));
        self::assertEquals(2600, $calculator->calculateFleetTravelTime(1, 1, 10, 10, 1));

        // Level 2 -> 5% faster, level 10 -> 45% faster.
        self::assertEquals(2470, $calculator->calculateFleetTravelTime(1, 1, 10, 10, 2));
        self::assertEquals(1430, $calculator->calculateFleetTravelTime(1, 1, 10, 10, 10));
    }
}
