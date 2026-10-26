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
     * The number of steps between two tiles
     */
    public static function distance(int $x1, int $y1, int $x2, int $y2): int
    {
        [$q1, $r1] = self::toAxial($x1, $y1);
        [$q2, $r2] = self::toAxial($x2, $y2);
        $dq = $q1 - $q2;
        $dr = $r1 - $r2;

        return intdiv(abs($dq) + abs($dr) + abs($dq + $dr), 2);
    }

    /**
     * @return non-empty-list<array{int, int}> The tiles a straight line from one tile to another crosses,
     *     including both
     */
    public static function line(int $x1, int $y1, int $x2, int $y2): array
    {
        $steps = self::distance($x1, $y1, $x2, $y2);
        if ($steps === 0) {
            return [[$x1, $y1]];
        }

        // A tiny nudge keeps the line from running exactly along tile edges, where rounding would be ambiguous
        [$q1, $r1] = self::toAxial($x1, $y1);
        [$q2, $r2] = self::toAxial($x2, $y2);
        $q1 += 1e-6;
        $r1 += 2e-6;

        $tiles = [[$x1, $y1]];
        for ($i = 1; $i <= $steps; $i++) {
            $t = $i / $steps;
            $tiles[] = self::fromAxial(...self::roundAxial($q1 + ($q2 - $q1) * $t, $r1 + ($r2 - $r1) * $t));
        }

        return $tiles;
    }

    /**
     * @return array{int, int}
     */
    private static function toAxial(int $x, int $y): array
    {
        return [$x - intdiv($y - ($y & 1), 2), $y];
    }

    /**
     * @return array{int, int}
     */
    private static function fromAxial(int $q, int $r): array
    {
        return [$q + intdiv($r - ($r & 1), 2), $r];
    }

    /**
     * @return array{int, int}
     */
    private static function roundAxial(float $q, float $r): array
    {
        $s = -$q - $r;
        $roundedQ = round($q);
        $roundedR = round($r);
        $roundedS = round($s);

        $diffQ = abs($roundedQ - $q);
        $diffR = abs($roundedR - $r);
        $diffS = abs($roundedS - $s);

        if ($diffQ > $diffR && $diffQ > $diffS) {
            $roundedQ = -$roundedR - $roundedS;
        } elseif ($diffR > $diffS) {
            $roundedR = -$roundedQ - $roundedS;
        }

        return [(int) $roundedQ, (int) $roundedR];
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
