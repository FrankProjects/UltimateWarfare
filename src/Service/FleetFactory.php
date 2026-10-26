<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\FleetMovement;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;

final class FleetFactory
{
    private DistanceCalculator $distanceCalculator;

    public function __construct(DistanceCalculator $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }

    /**
     * @param non-empty-list<array{int, int}> $path The route from the source to the target region
     */
    public function createForPlayer(
        Player $player,
        WorldRegion $worldRegion,
        WorldRegion $targetWorldRegion,
        int $travelSpeed,
        array $path,
        FleetMovement $movement
    ): Fleet {
        // Aircraft fly in a straight line, other fleets travel region by region
        $distance = $movement === FleetMovement::AIR
            ? $this->distanceCalculator->calculateDistance(
                $targetWorldRegion->getX(),
                $targetWorldRegion->getY(),
                $worldRegion->getX(),
                $worldRegion->getY()
            )
            : $this->distanceCalculator->calculatePathDistance($path);

        $travelTime = $this->distanceCalculator->calculateFleetTravelTime(
            $distance,
            $worldRegion->getUnitLevel(GameUnitEnum::TRAIN_STATION),
            $travelSpeed
        );

        $fleet = new Fleet();
        $fleet->setPlayer($player);
        $fleet->setWorldRegion($worldRegion);
        $fleet->setTargetWorldRegion($targetWorldRegion);
        $fleet->setPath($path);
        $now = time();
        $fleet->setTimestamp($now);
        $fleet->setTimestampArrive($now + $travelTime);

        return $fleet;
    }
}
