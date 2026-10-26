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

    public function testDistanceCountsSteps(): void
    {
        self::assertSame(0, HexGrid::distance(5, 4, 5, 4));
        foreach (HexGrid::getNeighborCoordinates(5, 3) as [$x, $y]) {
            self::assertSame(1, HexGrid::distance(5, 3, $x, $y));
        }
        self::assertSame(4, HexGrid::distance(0, 0, 4, 0));
        self::assertSame(4, HexGrid::distance(0, 0, 2, 4));
        self::assertSame(3, HexGrid::distance(2, 1, 0, 3));
    }

    public function testLineCrossesEveryTileBetweenBothEnds(): void
    {
        $line = HexGrid::line(0, 0, 3, 5);

        self::assertCount(HexGrid::distance(0, 0, 3, 5) + 1, $line);
        self::assertSame([0, 0], $line[0]);
        self::assertSame([3, 5], $line[count($line) - 1]);
        for ($i = 1; $i < count($line); $i++) {
            self::assertSame(1, HexGrid::distance($line[$i - 1][0], $line[$i - 1][1], $line[$i][0], $line[$i][1]));
        }
    }

    public function testLineOfOneTile(): void
    {
        self::assertSame([[2, 2]], HexGrid::line(2, 2, 2, 2));
    }

    public function testSearchFindsShortestPathAroundBlockedTiles(): void
    {
        // Row 0 tile (1, 0) is blocked, so the path goes through row 1
        $open = ['0,0', '0,1', '1,1', '2,0'];
        $paths = HexGrid::search(
            0,
            0,
            static fn (array $from, array $to): bool => in_array($to[0] . ',' . $to[1], $open, true),
            static fn (array $tile): bool => true
        );

        self::assertSame([[0, 0], [0, 1], [1, 1], [2, 0]], $paths['2,0']);
        self::assertArrayNotHasKey('1,0', $paths);
    }

    public function testSearchDoesNotMoveOnFromTilesItCannotPassThrough(): void
    {
        $paths = HexGrid::search(
            0,
            0,
            static fn (array $from, array $to): bool => $to[1] === 0 && $to[0] >= 0 && $to[0] <= 3,
            static fn (array $tile): bool => $tile !== [2, 0]
        );

        self::assertSame([[0, 0], [1, 0], [2, 0]], $paths['2,0']);
        self::assertArrayNotHasKey('3,0', $paths);
    }
}
