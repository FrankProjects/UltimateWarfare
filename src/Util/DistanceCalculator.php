<?php

namespace FrankProjects\UltimateWarfare\Util;

final class DistanceCalculator
{
    public function calculateDistance(int $targetX, int $targetY, int $sourceX, int $sourceY): int
    {
        $differenceX = abs($targetX - $sourceX);
        $differenceY = abs($targetY - $sourceY);

        $distance = pow($differenceX, 2) + pow($differenceY, 2);
        return intval(2 * round(sqrt($distance)));
    }
}
