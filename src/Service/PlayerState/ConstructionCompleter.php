<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\PlayerState;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;

final class ConstructionCompleter
{
    private ConstructionRepository $constructionRepository;
    private ReportRepository $reportRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(
        ConstructionRepository $constructionRepository,
        ReportRepository $reportRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        NetWorthUpdaterService $netWorthUpdaterService,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->reportRepository = $reportRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    /**
     * Completes a construction queue item. Income and upkeep are not touched here: the PlayerStateUpdaterService
     * accrues resources up to the completion time first and recalculates the rate afterwards.
     *
     * @return bool Whether the construction was completed (false when the region changed owner)
     */
    public function complete(Construction $construction): bool
    {
        $worldRegion = $construction->getWorldRegion();

        if (
            $worldRegion->getPlayer() === null
            || $worldRegion->getPlayer()->getId() !== $construction->getPlayer()->getId()
        ) {
            // Never process construction queue items for a region that no longer belongs to this player
            $this->constructionRepository->remove($construction);
            return false;
        }

        $gameUnit = $this->gameUnitRegistry->find($construction->getGameUnit());

        if ($gameUnit->getGameUnitCategory()->isLeveled()) {
            $this->processLeveledConstruction($construction, $gameUnit->getBattleStats()->getHealth());
        } else {
            $this->processStackableConstruction($construction);
        }

        $this->createConstructionReport($construction);
        $this->constructionRepository->remove($construction);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($construction->getPlayer());

        return true;
    }

    /**
     * Building or upgrading a leveled building: bump the level by one and (re)fill its
     * health pool to the maximum for the new level.
     */
    private function processLeveledConstruction(Construction $construction, int $baseHealth): void
    {
        $worldRegionLeveledUnit = $this->getWorldRegionLeveledUnit($construction);
        $newLevel = ($worldRegionLeveledUnit?->getLevel() ?? 0) + 1;
        $maxHealth = $baseHealth * $newLevel;

        if ($worldRegionLeveledUnit !== null) {
            $worldRegionLeveledUnit->setLevel($newLevel);
            $worldRegionLeveledUnit->setHealth($maxHealth);
        } else {
            $worldRegionLeveledUnit = WorldRegionLeveledUnit::create(
                $construction->getWorldRegion(),
                $construction->getGameUnit(),
                $newLevel,
                $maxHealth
            );
        }

        $this->worldRegionLeveledUnitRepository->save($worldRegionLeveledUnit);
    }

    private function processStackableConstruction(Construction $construction): void
    {
        $worldRegionStackableUnit = $this->getWorldRegionStackableUnit($construction);

        if ($worldRegionStackableUnit !== null) {
            $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() + $construction->getNumber());
        } else {
            $worldRegionStackableUnit = WorldRegionStackableUnit::create(
                $construction->getWorldRegion(),
                $construction->getGameUnit(),
                $construction->getNumber()
            );
        }

        $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
    }

    private function createConstructionReport(Construction $construction): void
    {
        $gameUnit = $this->gameUnitRegistry->find($construction->getGameUnit());
        $reportType = Report::TYPE_GENERAL;
        if ($construction->getNumber() > 1) {
            $unitName = $gameUnit->getNameMulti();
            $message = "You completed {$construction->getNumber()} {$unitName}!";
        } else {
            $unitName = $gameUnit->getName();
            $message = "You completed {$construction->getNumber()} {$unitName}!";
        }

        $finishedConstructionTime = $construction->getCompletionTimestamp();
        $report = Report::createForPlayer($construction->getPlayer(), $finishedConstructionTime, $reportType, $message);
        $this->reportRepository->save($report);
    }

    private function getWorldRegionStackableUnit(Construction $construction): ?WorldRegionStackableUnit
    {
        $worldRegion = $construction->getWorldRegion();
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnitObject) {
            if ($worldRegionStackableUnitObject->getGameUnit() === $construction->getGameUnit()) {
                return $worldRegionStackableUnitObject;
            }
        }

        return null;
    }

    private function getWorldRegionLeveledUnit(Construction $construction): ?WorldRegionLeveledUnit
    {
        return $construction->getWorldRegion()->getLeveledUnit($construction->getGameUnit());
    }
}
