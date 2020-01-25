<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;

final class BattleUpdaterService
{
    private FleetRepository $fleetRepository;
    private FleetUnitRepository $fleetUnitRepository;
    private WorldRegionRepository $worldRegionRepository;
    private WorldRegionUnitRepository $worldRegionUnitRepository;

    public function __construct(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    /**
     * XXX TODO: Set player notifications
     *
     * @param Fleet $fleet
     * @param array $attackerGameUnits
     */
    public function updateBattleWon(Fleet $fleet, array $attackerGameUnits): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $targetWorldRegion->setPlayer($fleet->getPlayer());
        $this->worldRegionRepository->save($targetWorldRegion);

        foreach ($targetWorldRegion->getWorldRegionUnits() as $regionUnit) {
            $this->worldRegionUnitRepository->remove($regionUnit);
        }

        foreach ($attackerGameUnits as $fleetUnit) {
            $worldRegionUnit = WorldRegionUnit::create($targetWorldRegion, $fleetUnit->getGameUnit(), $fleetUnit->getAmount());
            $this->worldRegionUnitRepository->save($worldRegionUnit);
        }
        $this->fleetRepository->remove($fleet);
    }

    /**
     * XXX TODO: Improve code
     * XXX TODO: Set player notifications
     *
     * @param Fleet $fleet
     * @param array $attackerGameUnits
     * @param array $defenderGameUnits
     */
    public function updateBattleLost(Fleet $fleet, array $attackerGameUnits, array $defenderGameUnits): void
    {
        foreach ($fleet->getTargetWorldRegion()->getWorldRegionUnits() as $regionUnit) {
            $this->worldRegionUnitRepository->remove($regionUnit);
        }
        foreach ($defenderGameUnits as $worldRegionUnit) {
            $this->worldRegionUnitRepository->save($worldRegionUnit);
        }
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->fleetUnitRepository->remove($fleetUnit);
        }
        foreach ($attackerGameUnits as $fleetUnit) {
            $this->fleetUnitRepository->save($fleetUnit);
        }
    }
}
