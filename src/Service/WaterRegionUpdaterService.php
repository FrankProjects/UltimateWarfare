<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use RuntimeException;

/**
 * Water regions can't be bought: a player claims one by stationing naval units on it, and it stays claimed only
 * while the owner has units, a Sea Mine or a Sea Mine under construction on it.
 */
final class WaterRegionUpdaterService
{
    private WorldRegionRepository $worldRegionRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private EntityLockRepository $entityLockRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        NetWorthUpdaterService $netWorthUpdaterService,
        EntityLockRepository $entityLockRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->entityLockRepository = $entityLockRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * The caller must hold a row lock on the region (see EntityLockRepository::lockForUpdate())
     */
    public function claim(WorldRegion $worldRegion, Player $player): void
    {
        if (!$worldRegion->isWater()) {
            throw new RuntimeException('Only water regions can be claimed!');
        }

        if ($worldRegion->getPlayer() !== null) {
            throw new RuntimeException('Region is already owned by somebody!');
        }

        $worldRegion->setPlayer($player);
        // Keep both sides in sync, the net worth below counts the player's regions
        $player->getWorldRegions()->add($worldRegion);
        $this->worldRegionRepository->save($worldRegion);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
    }

    public function releaseIfUnoccupied(WorldRegion $worldRegion): void
    {
        $player = $worldRegion->getPlayer();
        if ($player === null || !$worldRegion->isWater() || $this->isOccupied($worldRegion)) {
            return;
        }

        $worldRegion->setPlayer(null);
        $player->getWorldRegions()->removeElement($worldRegion);
        $this->worldRegionRepository->save($worldRegion);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
    }

    /**
     * Backstop for the queue worker: releases water regions that lost their last units in a way that doesn't
     * release them right away, such as an operation
     */
    public function releaseUnoccupiedRegions(): void
    {
        foreach ($this->worldRegionRepository->findUnoccupiedOwnedWaterRegions() as $worldRegion) {
            $player = $worldRegion->getPlayer();
            if ($player === null) {
                continue;
            }

            $this->entityManager->beginTransaction();

            try {
                // Lock the player before the region, in the same order as player requests do
                if (
                    $this->entityLockRepository->lockForUpdate($player)
                    && $this->entityLockRepository->lockForUpdate($worldRegion)
                ) {
                    $this->releaseIfUnoccupied($worldRegion);
                }

                $this->entityManager->commit();
            } catch (\Throwable $e) {
                $this->entityManager->rollback();
                throw $e;
            }
        }
    }

    private function isOccupied(WorldRegion $worldRegion): bool
    {
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() > 0) {
                return true;
            }
        }

        return !$worldRegion->getWorldRegionLeveledUnits()->isEmpty() || !$worldRegion->getConstructions()->isEmpty();
    }
}
