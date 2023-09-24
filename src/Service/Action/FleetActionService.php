<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use RuntimeException;

final class FleetActionService
{
    private FleetRepository $fleetRepository;
    private FleetUnitRepository $fleetUnitRepository;
    private GameUnitRepository $gameUnitRepository;
    private WorldRegionUnitRepository $worldRegionUnitRepository;

    public function __construct(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository,
        GameUnitRepository $gameUnitRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->gameUnitRepository = $gameUnitRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    public function recall(int $fleetId, Player $player): bool
    {
        $fleet = $this->getFleetByIdAndPlayer($fleetId, $player);

        if ($fleet->getWorldRegion()->getPlayer()->getId() != $player->getId()) {
            throw new RuntimeException('You are not the owner of this region!');
        }

        $this->addFleetUnitsToWorldRegion($fleet, $fleet->getWorldRegion());

        return true;
    }

    public function reinforce(int $fleetId, Player $player): bool
    {
        $fleet = $this->getFleetByIdAndPlayer($fleetId, $player);

        if ($fleet->getTargetWorldRegion()->getPlayer()->getId() != $player->getId()) {
            throw new RuntimeException('You are not the owner of this region!');
        }

        $this->addFleetUnitsToWorldRegion($fleet, $fleet->getTargetWorldRegion());

        return true;
    }

    /**
     * @param array<int, string> $unitData
     */
    public function sendGameUnits(
        WorldRegion $region,
        WorldRegion $targetRegion,
        Player $player,
        GameUnitType $gameUnitType,
        array $unitData
    ): void {
        if ($targetRegion->getWorld()->getId() != $player->getWorld()->getId()) {
            throw new RuntimeException('Target region does not exist!');
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            throw new RuntimeException('Region is not owned by you.');
        }

        $fleet = Fleet::createForPlayer($player, $region, $targetRegion);
        $this->fleetRepository->save($fleet);

        foreach ($unitData as $gameUnitId => $amount) {
            $amount = intval($amount);
            if ($amount < 1) {
                continue;
            }

            $gameUnit = $this->gameUnitRepository->find($gameUnitId);
            if ($gameUnit === null) {
                continue;
            }

            if ($gameUnit->getGameUnitType()->getId() !== $gameUnitType->getId()) {
                continue;
            }

            $this->addFleetUnitToFleet($region, $gameUnit, $amount, $fleet);
        }
    }

    private function getFleetByIdAndPlayer(int $fleetId, Player $player): Fleet
    {
        $fleet = $this->fleetRepository->findByIdAndPlayer($fleetId, $player);

        if ($fleet === null) {
            throw new RuntimeException('Fleet does not exist!');
        }

        return $fleet;
    }

    private function addFleetUnitsToWorldRegion(Fleet $fleet, WorldRegion $worldRegion): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->addFleetUnitToWorldRegion($fleetUnit, $worldRegion);
        }

        $this->fleetRepository->remove($fleet);
    }

    private function addFleetUnitToWorldRegion(FleetUnit $fleetUnit, WorldRegion $worldRegion): void
    {
        $found = false;
        foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($fleetUnit->getGameUnit()->getId() === $worldRegionUnit->getGameUnit()->getId()) {
                $worldRegionUnit->setAmount($worldRegionUnit->getAmount() + $fleetUnit->getAmount());
                $this->worldRegionUnitRepository->save($worldRegionUnit);
                $found = true;
                break;
            }
        }

        if ($found === false) {
            $worldRegionUnit = WorldRegionUnit::create(
                $worldRegion,
                $fleetUnit->getGameUnit(),
                $fleetUnit->getAmount()
            );
            $this->worldRegionUnitRepository->save($worldRegionUnit);
        }
    }

    private function addFleetUnitToFleet(WorldRegion $region, GameUnit $gameUnit, int $amount, Fleet $fleet): void
    {
        $hasUnit = false;
        foreach ($region->getWorldRegionUnits() as $regionUnit) {
            if ($regionUnit->getGameUnit()->getId() == $gameUnit->getId()) {
                $hasUnit = true;
                if ($amount > $regionUnit->getAmount()) {
                    throw new RuntimeException("You don't have that many " . $gameUnit->getName() . "s!");
                }

                $regionUnit->setAmount($regionUnit->getAmount() - $amount);

                $fleetUnit = FleetUnit::createForFleet($fleet, $regionUnit->getGameUnit(), $amount);
                $this->fleetUnitRepository->save($fleetUnit);

                if ($regionUnit->getAmount() === 0) {
                    $this->worldRegionUnitRepository->remove($regionUnit);
                } else {
                    $this->worldRegionUnitRepository->save($regionUnit);
                }
                break;
            }
        }

        if ($hasUnit !== true) {
            throw new RuntimeException("You don't have that many " . $gameUnit->getName() . "s!");
        }
    }
}
