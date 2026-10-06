<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;

final class BattleUpdaterService
{
    private FleetRepository $fleetRepository;
    private FleetUnitRepository $fleetUnitRepository;
    private WorldRegionRepository $worldRegionRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;

    public function __construct(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
    }

    /**
     * XXX TODO: Set player notifications
     *
     * @param Fleet $fleet
     * @param array<FleetUnit> $attackerGameUnits
     */
    public function updateBattleWon(Fleet $fleet, array $attackerGameUnits): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $targetWorldRegion->setPlayer($fleet->getPlayer());
        $this->worldRegionRepository->save($targetWorldRegion);

        foreach ($targetWorldRegion->getWorldRegionStackableUnits() as $regionUnit) {
            $this->worldRegionStackableUnitRepository->remove($regionUnit);
        }
        foreach ($targetWorldRegion->getWorldRegionLeveledUnits() as $leveledUnit) {
            $this->worldRegionLeveledUnitRepository->remove($leveledUnit);
        }

        foreach ($attackerGameUnits as $fleetUnit) {
            $worldRegionStackableUnit = WorldRegionStackableUnit::create(
                $targetWorldRegion,
                $fleetUnit->getGameUnit(),
                $fleetUnit->getAmount()
            );
            $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
        }
        $this->fleetRepository->remove($fleet);
    }

    /**
     * XXX TODO: Improve code
     * XXX TODO: Set player notifications
     *
     * @param Fleet $fleet
     * @param array<FleetUnit> $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    public function updateBattleLost(Fleet $fleet, array $attackerGameUnits, array $defenderGameUnits): void
    {
        // Defenders missing from $defenderGameUnits died in battle; survivors are updated in place
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        foreach ($targetWorldRegion->getWorldRegionStackableUnits()->toArray() as $regionUnit) {
            if (!in_array($regionUnit, $defenderGameUnits, true)) {
                $this->worldRegionStackableUnitRepository->remove($regionUnit);
            }
        }
        foreach ($targetWorldRegion->getWorldRegionLeveledUnits()->toArray() as $leveledUnit) {
            if (!in_array($leveledUnit, $defenderGameUnits, true)) {
                $this->worldRegionLeveledUnitRepository->remove($leveledUnit);
            }
        }
        foreach ($defenderGameUnits as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit instanceof WorldRegionLeveledUnit) {
                $this->worldRegionLeveledUnitRepository->save($worldRegionStackableUnit);
            } else {
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
            }
        }
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->fleetUnitRepository->remove($fleetUnit);
        }
        foreach ($attackerGameUnits as $fleetUnit) {
            $this->fleetUnitRepository->save($fleetUnit);
        }
    }
}
