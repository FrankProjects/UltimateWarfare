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

    /**
     * Breadth-first search from a tile, one tile per step. A tile is reached when $canEnter allows the step onto it,
     * the search only moves on from tiles $canPassThrough allows; the start tile is always passed through.
     *
     * @param callable(array{int, int} $from, array{int, int} $to): bool $canEnter
     * @param callable(array{int, int} $tile): bool $canPassThrough
     * @return array<string, non-empty-list<array{int, int}>> The path from the start to every reached tile,
     *     keyed by "x,y"
     */
    public static function search(int $x, int $y, callable $canEnter, callable $canPassThrough): array
    {
        $paths = [$x . ',' . $y => [[$x, $y]]];
        $queue = [[$x, $y]];

        while ($queue !== []) {
            $tile = array_shift($queue);
            $path = $paths[$tile[0] . ',' . $tile[1]];

            foreach (self::getNeighborCoordinates($tile[0], $tile[1]) as $neighbor) {
                $key = $neighbor[0] . ',' . $neighbor[1];
                if (isset($paths[$key]) || !$canEnter($tile, $neighbor)) {
                    continue;
                }

                $paths[$key] = [...$path, $neighbor];
                if ($canPassThrough($neighbor)) {
                    $queue[] = $neighbor;
                }
            }
        }

        return $paths;
    }
}
