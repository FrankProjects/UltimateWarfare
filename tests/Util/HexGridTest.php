<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Util;

use FrankProjects\UltimateWarfare\Util\HexGrid;
use PHPUnit\Framework\TestCase;

class HexGridTest extends TestCase
{
    public function testNeighborsOfEvenRowAreShiftedLeft(): void
    {
        self::assertEqualsCanonicalizing(
            [[4, 4], [6, 4], [4, 3], [5, 3], [4, 5], [5, 5]],
            HexGrid::getNeighborCoordinates(5, 4)
        );
    }

    public function testNeighborsOfOddRowAreShiftedRight(): void
    {
        self::assertEqualsCanonicalizing(
            [[4, 3], [6, 3], [5, 2], [6, 2], [5, 4], [6, 4]],
            HexGrid::getNeighborCoordinates(5, 3)
        );
    }
}
