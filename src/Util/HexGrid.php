<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

/**
 * The world map is a grid of pointy-top hexagons in odd-r offset coordinates: odd rows are shifted right
 */
final class HexGrid
{
    /**
     * @return list<array{int, int}> The coordinates of the 6 neighbors of a tile
     */
    public static function getNeighborCoordinates(int $x, int $y): array
    {
        if ($y % 2 === 0) {
            return [
                [$x - 1, $y], [$x + 1, $y],
                [$x - 1, $y - 1], [$x, $y - 1],
                [$x - 1, $y + 1], [$x, $y + 1],
            ];
        }

        return [
            [$x - 1, $y], [$x + 1, $y],
            [$x, $y - 1], [$x + 1, $y - 1],
            [$x, $y + 1], [$x + 1, $y + 1],
        ];
    }
}
