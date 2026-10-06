<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

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

    public function createForPlayer(
        Player $player,
        WorldRegion $worldRegion,
        WorldRegion $targetWorldRegion
    ): Fleet {
        $travelTime = $this->distanceCalculator->calculateFleetTravelTime(
            $targetWorldRegion->getX(),
            $targetWorldRegion->getY(),
            $worldRegion->getX(),
            $worldRegion->getY(),
            $worldRegion->getUnitLevel(GameUnitEnum::TRAIN_STATION)
        );

        $fleet = new Fleet();
        $fleet->setPlayer($player);
        $fleet->setWorldRegion($worldRegion);
        $fleet->setTargetWorldRegion($targetWorldRegion);
        $fleet->setTimestamp(time());
        $fleet->setTimestampArrive(time() + $travelTime);

        return $fleet;
    }
}
