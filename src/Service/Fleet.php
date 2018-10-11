<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Fleet as FleetEntity;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use RuntimeException;

final class Fleet
{
    /**
     * @var FleetRepository
     */
    private $fleetRepository;

    /**
     * @var WorldRegionUnitRepository
     */
    private $worldRegionUnitRepository;

    /**
     * Fleet service
     *
     * @param FleetRepository $fleetRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     */
    public function __construct(FleetRepository $fleetRepository, WorldRegionUnitRepository $worldRegionUnitRepository)
    {
        $this->fleetRepository = $fleetRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    /**
     * @param int $fleetId
     * @param Player $player
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function recall(int $fleetId, Player $player): bool
    {
        $fleet = $this->getFleetByIdAndPlayer($fleetId, $player);

        if ($fleet === null) {
            throw new RunTimeException('Fleet does not exist.');
        }

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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function reinforce(int $fleetId, Player $player): bool
    {
        $fleet = $this->getFleetByIdAndPlayer($fleetId, $player);

        if ($fleet === null) {
            throw new RunTimeException('Fleet does not exist.');
        }

        if ($fleet->getTargetWorldRegion()->getPlayer()->getId() != $player->getId()) {
            throw new RunTimeException('You are not the owner of this region!');
        }

        $this->addFleetUnitsToWorldRegion($fleet, $fleet->getTargetWorldRegion());

        return true;
    }

    /**
     * @param int $fleetId
     * @param Player $player
     * @return FleetEntity|null
     */
    private function getFleetByIdAndPlayer(int $fleetId, Player $player): ?FleetEntity
    {
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            return null;
        }

        if ($fleet->getPlayer()->getId() != $player->getId()) {
            return null;
        }

        return $fleet;
    }

    /**
     * @param FleetEntity $fleet
     * @param WorldRegion $worldRegion
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function addFleetUnitsToWorldRegion(FleetEntity $fleet, WorldRegion $worldRegion): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->addFleetUnitToWorldRegion($fleetUnit, $worldRegion);
        }

        $this->fleetRepository->remove($fleet);
    }

    /**
     * @param FleetUnit $fleetUnit
     * @param WorldRegion $worldRegion
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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
}
