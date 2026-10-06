<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\FleetFactory;
use RuntimeException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class FleetActionService
{
    private FleetRepository $fleetRepository;
    private FleetUnitRepository $fleetUnitRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private EntityManagerInterface $entityManager;
    private FleetFactory $fleetFactory;

    public function __construct(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository,
        GameUnitRegistry $gameUnitRegistry,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        EntityManagerInterface $entityManager,
        FleetFactory $fleetFactory
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->entityManager = $entityManager;
        $this->fleetFactory = $fleetFactory;
    }

    public function recall(int $fleetId, Player $player): WorldRegion
    {
        // Begin transaction for atomic operation
        $this->entityManager->beginTransaction();

        try {
            // Get and lock the fleet to prevent race conditions
            $fleet = $this->entityManager->find(
                Fleet::class,
                $fleetId,
                LockMode::PESSIMISTIC_WRITE
            );

            if ($fleet === null || $fleet->getPlayer()->getId() !== $player->getId()) {
                throw new RuntimeException('Fleet does not exist!');
            }

            $sourceRegion = $fleet->getWorldRegion();
            $targetPlayer = $sourceRegion->getPlayer();
            if ($targetPlayer === null || $targetPlayer->getId() !== $player->getId()) {
                throw new RuntimeException('You are not the owner of this region!');
            }

            $this->addFleetUnitsToWorldRegion($fleet, $sourceRegion);

            $this->entityManager->commit();

            return $sourceRegion;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function reinforce(int $fleetId, Player $player): WorldRegion
    {
        // Begin transaction for atomic operation
        $this->entityManager->beginTransaction();

        try {
            // Get and lock the fleet to prevent race conditions
            $fleet = $this->entityManager->find(
                Fleet::class,
                $fleetId,
                LockMode::PESSIMISTIC_WRITE
            );

            if ($fleet === null || $fleet->getPlayer()->getId() !== $player->getId()) {
                throw new RuntimeException('Fleet does not exist!');
            }

            $targetRegion = $fleet->getTargetWorldRegion();
            $targetPlayer = $targetRegion->getPlayer();

            if ($targetPlayer === null || $targetPlayer->getId() !== $player->getId()) {
                throw new RuntimeException('You are not the owner of this region!');
            }

            $this->addFleetUnitsToWorldRegion($fleet, $targetRegion);

            $this->entityManager->commit();

            return $targetRegion;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @param array<int, string> $unitData
     */
    public function sendGameUnits(
        WorldRegion $region,
        WorldRegion $targetRegion,
        Player $player,
        array $unitData
    ): Fleet {
        if ($targetRegion->getWorld()->getId() !== $player->getWorld()->getId()) {
            throw new RuntimeException('Target region does not exist!');
        }

        if ($region->getPlayer() === null || $region->getPlayer()->getId() !== $player->getId()) {
            throw new RuntimeException('Region is not owned by you.');
        }

        $gameUnitsToSend = [];
        foreach ($unitData as $gameUnitId => $amount) {
            $amount = intval($amount);
            if ($amount < 1) {
                continue;
            }

            $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitId);
            if ($gameUnitEnum === null) {
                continue;
            }

            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            if ($gameUnit->getGameUnitCategory()->isSendable() === false) {
                continue;
            }

            $gameUnitsToSend[] = [
                'gameUnit' => $gameUnit,
                'amount' => $amount,
            ];
        }

        if (count($gameUnitsToSend) === 0) {
            throw new RuntimeException('No game units selected to send!');
        }

        $fleet = $this->fleetFactory->createForPlayer($player, $region, $targetRegion);
        $this->fleetRepository->save($fleet);

        foreach ($gameUnitsToSend as $gameUnitData) {
            $this->addFleetUnitToFleet($region, $gameUnitData['gameUnit'], $gameUnitData['amount'], $fleet);
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
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($fleetUnit->getGameUnit() === $worldRegionStackableUnit->getGameUnit()) {
                $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() + $fleetUnit->getAmount());
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
                $found = true;
                break;
            }
        }

        if ($found === false) {
            $worldRegionStackableUnit = WorldRegionStackableUnit::create(
                $worldRegion,
                $fleetUnit->getGameUnit(),
                $fleetUnit->getAmount()
            );
            $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
        }
    }

    private function addFleetUnitToFleet(WorldRegion $region, GameUnit $gameUnit, int $amount, Fleet $fleet): void
    {
        $hasUnit = false;
        foreach ($region->getWorldRegionStackableUnits() as $regionUnit) {
            if ($regionUnit->getGameUnit() === $gameUnit->getGameUnitEnum()) {
                $hasUnit = true;
                if ($amount > $regionUnit->getAmount()) {
                    throw new RuntimeException("You don't have that many " . $gameUnit->getName() . "s!");
                }

                $regionUnit->setAmount($regionUnit->getAmount() - $amount);

                $fleetUnit = FleetUnit::createForFleet($fleet, $regionUnit->getGameUnit(), $amount);
                $this->fleetUnitRepository->save($fleetUnit);

                if ($regionUnit->getAmount() === 0) {
                    $this->worldRegionStackableUnitRepository->remove($regionUnit);
                } else {
                    $this->worldRegionStackableUnitRepository->save($regionUnit);
                }
                break;
            }
        }

        if ($hasUnit !== true) {
            throw new RuntimeException("You don't have that many " . $gameUnit->getName() . "s!");
        }
    }
}
