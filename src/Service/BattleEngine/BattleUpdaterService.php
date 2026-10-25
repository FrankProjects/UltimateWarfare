<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\WaterRegionUpdaterService;
use RuntimeException;

final class BattleUpdaterService
{
    private FleetRepository $fleetRepository;
    private FleetUnitRepository $fleetUnitRepository;
    private WorldRegionRepository $worldRegionRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;
    private ConstructionRepository $constructionRepository;
    private PlayerRepository $playerRepository;
    private WaterRegionUpdaterService $waterRegionUpdaterService;

    public function __construct(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        ConstructionRepository $constructionRepository,
        PlayerRepository $playerRepository,
        WaterRegionUpdaterService $waterRegionUpdaterService
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
        $this->constructionRepository = $constructionRepository;
        $this->playerRepository = $playerRepository;
        $this->waterRegionUpdaterService = $waterRegionUpdaterService;
    }

    /**
     * @param Fleet $fleet
     * @param array<FleetUnit> $attackerGameUnits
     */
    public function updateBattleWon(Fleet $fleet, array $attackerGameUnits): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        // Notify before the region changes owner, so the defender is still known
        $this->notifyDefender($this->getDefender($targetWorldRegion));

        $targetWorldRegion->setPlayer($fleet->getPlayer());
        $this->worldRegionRepository->save($targetWorldRegion);

        // All defenders are lost, as is the construction queue of the previous owner
        $this->removeDeadDefenders($targetWorldRegion, []);
        foreach ($targetWorldRegion->getConstructions()->toArray() as $construction) {
            $this->constructionRepository->remove($construction);
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
     * @param Fleet $fleet
     * @param array<FleetUnit> $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    public function updateBattleLost(Fleet $fleet, array $attackerGameUnits, array $defenderGameUnits): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $this->notifyDefender($this->getDefender($targetWorldRegion));

        $this->removeDeadDefenders($targetWorldRegion, $defenderGameUnits);
        $this->saveSurvivingDefenders($defenderGameUnits);
        $this->updateAttackingFleet($fleet, $attackerGameUnits);

        // A water region whose defenders all died is no longer held by the defender
        $this->waterRegionUpdaterService->releaseIfUnoccupied($targetWorldRegion);
    }

    /**
     * Defenders missing from $survivingDefenderGameUnits died in battle
     *
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $survivingDefenderGameUnits
     */
    private function removeDeadDefenders(WorldRegion $worldRegion, array $survivingDefenderGameUnits): void
    {
        foreach ($worldRegion->getWorldRegionStackableUnits()->toArray() as $stackableUnit) {
            if (!in_array($stackableUnit, $survivingDefenderGameUnits, true)) {
                $this->worldRegionStackableUnitRepository->remove($stackableUnit);
            }
        }
        foreach ($worldRegion->getWorldRegionLeveledUnits()->toArray() as $leveledUnit) {
            if (!in_array($leveledUnit, $survivingDefenderGameUnits, true)) {
                $this->worldRegionLeveledUnitRepository->remove($leveledUnit);
            }
        }
    }

    /**
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $survivingDefenderGameUnits
     */
    private function saveSurvivingDefenders(array $survivingDefenderGameUnits): void
    {
        foreach ($survivingDefenderGameUnits as $defenderGameUnit) {
            if ($defenderGameUnit instanceof WorldRegionLeveledUnit) {
                $this->worldRegionLeveledUnitRepository->save($defenderGameUnit);
            } else {
                $this->worldRegionStackableUnitRepository->save($defenderGameUnit);
            }
        }
    }

    /**
     * Attackers missing from $survivingAttackerGameUnits died in battle; survivors stay in the fleet at the target
     *
     * @param array<FleetUnit> $survivingAttackerGameUnits
     */
    private function updateAttackingFleet(Fleet $fleet, array $survivingAttackerGameUnits): void
    {
        foreach ($fleet->getFleetUnits()->toArray() as $fleetUnit) {
            if (!in_array($fleetUnit, $survivingAttackerGameUnits, true)) {
                $fleet->getFleetUnits()->removeElement($fleetUnit);
                $this->fleetUnitRepository->remove($fleetUnit);
            }
        }

        if ($fleet->getFleetUnits()->isEmpty()) {
            // The whole fleet was destroyed
            $this->fleetRepository->remove($fleet);
            return;
        }

        foreach ($survivingAttackerGameUnits as $fleetUnit) {
            $this->fleetUnitRepository->save($fleetUnit);
        }
    }

    private function getDefender(WorldRegion $worldRegion): Player
    {
        $defender = $worldRegion->getPlayer();
        if ($defender === null) {
            throw new RuntimeException("Target region has no owner");
        }

        return $defender;
    }

    private function notifyDefender(Player $defender): void
    {
        $defender->getNotifications()->setAttacked(true);
        $this->playerRepository->save($defender);
    }
}
