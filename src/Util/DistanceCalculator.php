<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class DistanceCalculator
{
    private int $fleetTravelTimeOverride;

    public function __construct(
        #[Autowire(param: 'app.uw_fleet_travel_time_override')]
        int $fleetTravelTimeOverride
    ) {
        $this->fleetTravelTimeOverride = $fleetTravelTimeOverride;
    }

    public function calculateDistance(int $targetX, int $targetY, int $sourceX, int $sourceY): int
    {
        $differenceX = abs($targetX - $sourceX);
        $differenceY = abs($targetY - $sourceY);

        $distance = pow($differenceX, 2) + pow($differenceY, 2);
        return intval(2 * round(sqrt($distance)));
    }

    public function calculateDistanceTravelTime(int $targetX, int $targetY, int $sourceX, int $sourceY): int
    {
        if ($this->fleetTravelTimeOverride > 0) {
            return 1;
        }

        return $this->calculateDistance($targetX, $targetY, $sourceX, $sourceY) * 100;
    }

    /**
     * Travel time for a fleet, reduced by the Train Station level of the departing region
     * (5% per level above level 1, up to 45% at level 10).
     */
    public function calculateFleetTravelTime(
        int $targetX,
        int $targetY,
        int $sourceX,
        int $sourceY,
        int $trainStationLevel
    ): int {
        $travelTime = $this->calculateDistanceTravelTime($targetX, $targetY, $sourceX, $sourceY);

        if ($trainStationLevel >= 2) {
            $factor = 0.05 * ($trainStationLevel - 1);
            $travelTime = max(1, (int) round($travelTime * (1.0 - $factor)));
        }

        return $travelTime;
    }
}
