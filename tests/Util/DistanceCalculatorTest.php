<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Util;

use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use PHPUnit\Framework\TestCase;

class DistanceCalculatorTest extends TestCase
{
    public function testCalculateDistance()
    {
        $calculator = new DistanceCalculator();
        $result = $calculator->calculateDistance(1, 1, 1, 1);
        self::assertEquals(0, $result);

        $result = $calculator->calculateDistance(1, 1, 2, 1);
        self::assertEquals(2, $result);

        $result = $calculator->calculateDistance(1, 1, 10, 10);
        self::assertEquals(26, $result);
    }
}
