<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;

final class ConstructionProcessor implements Processor
{
    private ConstructionRepository $constructionRepository;
    private PlayerRepository $playerRepository;
    private ReportRepository $reportRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private GameUnitRegistry $gameUnitRegistry;
    private int $constructionTimeOverride;

    public function __construct(
        ConstructionRepository $constructionRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        NetWorthUpdaterService $netWorthUpdaterService,
        GameUnitRegistry $gameUnitRegistry,
        int $constructionTimeOverride
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->constructionTimeOverride = $constructionTimeOverride;
    }

    public function run(int $timestamp): void
    {
        // Override construction time for testing purposes
        if ($this->constructionTimeOverride > 0) {
            $constructions = $this->constructionRepository->getAllConstructions();
        } else {
            $constructions = $this->constructionRepository->getCompletedConstructions($timestamp);
        }

        foreach ($constructions as $construction) {
            $worldRegion = $construction->getWorldRegion();

            if (
                $worldRegion->getPlayer() === null
                || $worldRegion->getPlayer()->getId() !== $construction->getPlayer()->getId()
            ) {
                // Never process construction queue items for a region that no longer belongs to this player
                $this->constructionRepository->remove($construction);
                continue;
            }

            $this->processConstruction($construction);
        }
    }

    private function updatePlayerResources(Player $player, Construction $construction): Player
    {
        $gameUnit = $this->gameUnitRegistry->find($construction->getGameUnit());
        $upkeepCash = $construction->getNumber() * $gameUnit->getUpkeep()->getCash();
        $upkeepFood = $construction->getNumber() * $gameUnit->getUpkeep()->getFood();
        $upkeepWood = $construction->getNumber() * $gameUnit->getUpkeep()->getWood();
        $upkeepSteel = $construction->getNumber() * $gameUnit->getUpkeep()->getSteel();

        $incomeCash = $construction->getNumber() * $gameUnit->getIncome()->getCash();
        $incomeFood = $construction->getNumber() * $gameUnit->getIncome()->getFood();
        $incomeWood = $construction->getNumber() * $gameUnit->getIncome()->getWood();
        $incomeSteel = $construction->getNumber() * $gameUnit->getIncome()->getSteel();

        $income = $player->getIncome();
        $upkeep = $player->getUpkeep();

        $upkeep->addCash($upkeepCash);
        $upkeep->addFood($upkeepFood);
        $upkeep->addWood($upkeepWood);
        $upkeep->addSteel($upkeepSteel);

        $income->addCash($incomeCash);
        $income->addFood($incomeFood);
        $income->addWood($incomeWood);
        $income->addSteel($incomeSteel);

        $player->setIncome($income);
        $player->setUpkeep($upkeep);

        return $player;
    }

    private function processConstruction(Construction $construction): void
    {
        // XXX TODO: Process income before processing construction...
        //$this->processPlayerIncome($construction->getPlayer(), $timestamp);

        $gameUnit = $this->gameUnitRegistry->find($construction->getGameUnit());

        if ($gameUnit->getGameUnitCategory()->isLeveled()) {
            $this->processLeveledConstruction($construction, $gameUnit->getBattleStats()->getHealth());
        } else {
            $this->processStackableConstruction($construction);
        }

        $player = $this->updatePlayerResources($construction->getPlayer(), $construction);
        $this->createConstructionReport($construction);

        $this->playerRepository->save($player);
        $this->constructionRepository->remove($construction);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
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

        $finishedConstructionTime = $construction->getTimestamp() + $construction->getDuration();
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
