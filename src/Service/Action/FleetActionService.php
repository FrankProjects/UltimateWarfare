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
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\FleetFactory;
use FrankProjects\UltimateWarfare\Service\WaterRegionUpdaterService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
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
    private WorldRegionRepository $worldRegionRepository;
    private EntityLockRepository $entityLockRepository;
    private WaterRegionUpdaterService $waterRegionUpdaterService;

    public function __construct(
        FleetRepository $fleetRepository,
        FleetUnitRepository $fleetUnitRepository,
        GameUnitRegistry $gameUnitRegistry,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        EntityManagerInterface $entityManager,
        FleetFactory $fleetFactory,
        WorldRegionRepository $worldRegionRepository,
        EntityLockRepository $entityLockRepository,
        WaterRegionUpdaterService $waterRegionUpdaterService
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->entityManager = $entityManager;
        $this->fleetFactory = $fleetFactory;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->entityLockRepository = $entityLockRepository;
        $this->waterRegionUpdaterService = $waterRegionUpdaterService;
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
            // The water region the fleet left was released, so claim it again
            $this->claimIfOpenSea($sourceRegion, $player);

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
            if ($targetRegion->isWater() && $fleet->getTimestampArrive() > time()) {
                throw new RuntimeException('Fleet not arrived yet!');
            }
            $this->claimIfOpenSea($targetRegion, $player);

            $targetPlayer = $targetRegion->getPlayer();

            if ($targetPlayer === null || $targetPlayer->getId() !== $player->getId()) {
                throw new RuntimeException('You are not the owner of this region!');
            }

            // The Airfield of the target region may have been destroyed while the aircraft were on their way
            if ($this->hasAirUnits($fleet) && $targetRegion->getLeveledUnit(GameUnitEnum::AIRFIELD) === null) {
                throw new RuntimeException(
                    'Aircraft cannot land: this region no longer has an Airfield. Recall the fleet instead.'
                );
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

        if ($targetRegion->isWater()) {
            $this->validateWaterTarget($targetRegion, $player, $gameUnitsToSend);
        }

        $this->validateAirUnits($region, $targetRegion, $player, $gameUnitsToSend);

        $fleet = $this->fleetFactory->createForPlayer(
            $player,
            $region,
            $targetRegion,
            $this->getSlowestTravelSpeed($gameUnitsToSend)
        );
        $this->fleetRepository->save($fleet);

        foreach ($gameUnitsToSend as $gameUnitData) {
            $this->addFleetUnitToFleet($region, $gameUnitData['gameUnit'], $gameUnitData['amount'], $fleet);
        }

        $this->waterRegionUpdaterService->releaseIfUnoccupied($region);

        return $fleet;
    }

    /**
     * Aircraft take off from an Airfield and only move to regions with an Airfield. An attack needs no
     * Airfield on the target region, as aircraft never stay there but fly back to their source region.
     *
     * @param non-empty-list<array{gameUnit: GameUnit, amount: int}> $gameUnitsToSend
     */
    private function validateAirUnits(
        WorldRegion $region,
        WorldRegion $targetRegion,
        Player $player,
        array $gameUnitsToSend
    ): void {
        $hasAirUnits = false;
        foreach ($gameUnitsToSend as $gameUnitData) {
            if ($gameUnitData['gameUnit']->getGameUnitCategory() === GameUnitCategory::AIR_UNITS) {
                $hasAirUnits = true;
                break;
            }
        }

        if ($hasAirUnits === false) {
            return;
        }

        if ($region->getLeveledUnit(GameUnitEnum::AIRFIELD) === null) {
            throw new RuntimeException(
                'Aircraft cannot take off: this region has no Airfield. Build an Airfield here to move or '
                . 'attack with these aircraft.'
            );
        }

        $isAttack = $targetRegion->getPlayer() !== null && $targetRegion->getPlayer()->getId() !== $player->getId();
        if (!$isAttack && $targetRegion->getLeveledUnit(GameUnitEnum::AIRFIELD) === null) {
            throw new RuntimeException('Aircraft can only be moved to a region with an Airfield!');
        }
    }

    /**
     * Only ships sail onto water; open sea (unowned water) must be next to one of the player's regions
     *
     * @param non-empty-list<array{gameUnit: GameUnit, amount: int}> $gameUnitsToSend
     */
    private function validateWaterTarget(WorldRegion $targetRegion, Player $player, array $gameUnitsToSend): void
    {
        foreach ($gameUnitsToSend as $gameUnitData) {
            if ($gameUnitData['gameUnit']->getGameUnitCategory() !== GameUnitCategory::NAVAL_UNITS) {
                throw new RuntimeException('Only naval units can be sent to water!');
            }
        }

        if ($targetRegion->getPlayer() !== null) {
            return;
        }

        $adjacentRegions = $this->worldRegionRepository->findAdjacentRegions(
            $targetRegion->getX(),
            $targetRegion->getY(),
            $targetRegion->getWorld()
        );
        foreach ($adjacentRegions as $adjacentRegion) {
            if ($adjacentRegion->getPlayer()?->getId() === $player->getId()) {
                return;
            }
        }

        throw new RuntimeException('You can only sail to open sea next to one of your regions!');
    }

    /**
     * Claims an unowned water region for the player; a region owned by somebody is left as it is
     */
    private function claimIfOpenSea(WorldRegion $worldRegion, Player $player): void
    {
        if (!$worldRegion->isWater() || $worldRegion->getPlayer() !== null) {
            return;
        }

        // Another fleet may claim the same region concurrently; re-read it under a lock, claim() then refuses
        // a region that was claimed in the meantime
        if (!$this->entityLockRepository->lockForUpdate($worldRegion)) {
            return;
        }

        $this->waterRegionUpdaterService->claim($worldRegion, $player);
    }

    /**
     * A fleet moves at the speed of its slowest unit; units without a travel speed move at base speed.
     *
     * @param non-empty-list<array{gameUnit: GameUnit, amount: int}> $gameUnitsToSend
     */
    private function getSlowestTravelSpeed(array $gameUnitsToSend): int
    {
        $travelSpeeds = [];
        foreach ($gameUnitsToSend as $gameUnitData) {
            $travelSpeed = $gameUnitData['gameUnit']->getBattleStats()->getTravelSpeed();
            $travelSpeeds[] = $travelSpeed > 0 ? $travelSpeed : DistanceCalculator::BASE_TRAVEL_SPEED;
        }

        return min($travelSpeeds);
    }

    private function hasAirUnits(Fleet $fleet): bool
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $category = $this->gameUnitRegistry->find($fleetUnit->getGameUnit())->getGameUnitCategory();
            if ($category === GameUnitCategory::AIR_UNITS) {
                return true;
            }
        }

        return false;
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
