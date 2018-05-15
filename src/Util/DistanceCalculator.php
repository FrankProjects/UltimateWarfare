<?php

namespace FrankProjects\UltimateWarfare\Util;

final class DistanceCalculator
{
    /**
     * @param int $targetX
     * @param int $targetY
     * @param int $sourceX
     * @param int $sourceY
     * @return int
     */
    public function calculateDistance(int $targetX, int $targetY, int $sourceX, int $sourceY): int
    {
        $differenceX = abs($targetX - $sourceX);
        $differenceY = abs($targetY - $sourceY);

        $distance = pow($differenceX, 2) + pow($differenceY, 2);
        return 2 * round(sqrt($distance));
    }
}
