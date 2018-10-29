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
    /**
     * @var FleetRepository
     */
    private $fleetRepository;

    /**
     * @var FleetUnitRepository
     */
    private $fleetUnitRepository;

    /**
     * @var GameUnitRepository
     */
    private $gameUnitRepository;

    /**
     * @var WorldRegionUnitRepository
     */
    private $worldRegionUnitRepository;

    /**
     * FleetActionService service
     *
     * @param FleetRepository $fleetRepository
     * @param FleetUnitRepository $fleetUnitRepository
     * @param GameUnitRepository $gameUnitRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     */
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

    /**
     * @param int $fleetId
     * @param Player $player
     * @return bool
     */
    public function recall(int $fleetId, Player $player): bool
    {
        $fleet = $this->getFleetByIdAndPlayer($fleetId, $player);

        if ($fleet->getWorldRegion()->getPlayer()->getId() != $player->getId()) {
            throw new RunTimeException('You are not the owner of this region!');
        }

        $this->addFleetUnitsToWorldRegion($fleet, $fleet->getWorldRegion());

        return true;
    }

    /**
     * @param int $fleetId
     * @param Player $player
     * @return bool
     */
    public function reinforce(int $fleetId, Player $player): bool
    {
        $fleet = $this->getFleetByIdAndPlayer($fleetId, $player);

        if ($fleet->getTargetWorldRegion()->getPlayer()->getId() != $player->getId()) {
            throw new RunTimeException('You are not the owner of this region!');
        }

        $this->addFleetUnitsToWorldRegion($fleet, $fleet->getTargetWorldRegion());

        return true;
    }

    /**
     * @param WorldRegion $region
     * @param WorldRegion $targetRegion
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @param array $unitData
     * @throws \Exception
     */
    public function sendGameUnits(WorldRegion $region, WorldRegion $targetRegion, Player $player, GameUnitType $gameUnitType, array $unitData): void
    {
        $sector = $targetRegion->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            throw new RunTimeException('Target region does not exist!');
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            throw new RunTimeException('Region is not owned by you.');
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

    /**
     * @param int $fleetId
     * @param Player $player
     * @return Fleet
     */
    private function getFleetByIdAndPlayer(int $fleetId, Player $player): Fleet
    {
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            throw new RunTimeException('Fleet does not exist!');
        }

        if ($fleet->getPlayer()->getId() != $player->getId()) {
            throw new RunTimeException('Fleet does not belong to you!');
        }

        return $fleet;
    }

    /**
     * @param Fleet $fleet
     * @param WorldRegion $worldRegion
     */
    private function addFleetUnitsToWorldRegion(Fleet $fleet, WorldRegion $worldRegion): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->addFleetUnitToWorldRegion($fleetUnit, $worldRegion);
        }

        $this->fleetRepository->remove($fleet);
    }

    /**
     * @param FleetUnit $fleetUnit
     * @param WorldRegion $worldRegion
     */
    private function addFleetUnitToWorldRegion(FleetUnit $fleetUnit, WorldRegion $worldRegion): void
    {
        $worldRegionUnit = null;
        $found = false;
        foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($fleetUnit->getGameUnit()->getId() === $worldRegionUnit->getGameUnit()->getId()) {
                $worldRegionUnit->setAmount($worldRegionUnit->getAmount() + $fleetUnit->getAmount());
                $found = true;
                break;
            }
        }

        if ($found === false) {
            $worldRegionUnit = WorldRegionUnit::create($worldRegion, $fleetUnit->getGameUnit(), $fleetUnit->getAmount());
        }

        $this->worldRegionUnitRepository->save($worldRegionUnit);
    }

    /**
     * @param WorldRegion $region
     * @param GameUnit $gameUnit
     * @param int $amount
     * @param Fleet $fleet
     */
    private function addFleetUnitToFleet(WorldRegion $region, GameUnit $gameUnit, int $amount, Fleet $fleet): void
    {
        $hasUnit = false;
        foreach ($region->getWorldRegionUnits() as $regionUnit) {
            if ($regionUnit->getGameUnit()->getId() == $gameUnit->getId()) {
                $hasUnit = true;
                if ($amount > $regionUnit->getAmount()) {
                    throw new RunTimeException("You don't have that many " . $gameUnit->getName() . "s!");
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
            throw new RunTimeException("You don't have that many " . $gameUnit->getName() . "s!");
        }
    }
}
