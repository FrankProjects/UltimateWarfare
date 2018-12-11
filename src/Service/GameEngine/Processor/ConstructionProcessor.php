<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor;
use FrankProjects\UltimateWarfare\Service\NetworthUpdaterService;

final class ConstructionProcessor implements Processor
{
    /**
     * @var ConstructionRepository
     */
    private $constructionRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var WorldRegionUnitRepository
     */
    private $worldRegionUnitRepository;

    /**
     * @var NetworthUpdaterService
     */
    private $networthUpdaterService;

    /**
     * ConstructionProcessor constructor.
     *
     * @param ConstructionRepository $constructionRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     * @param NetworthUpdaterService $networthUpdaterService
     */
    public function __construct(
        ConstructionRepository $constructionRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository,
        NetworthUpdaterService $networthUpdaterService
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
        $this->networthUpdaterService = $networthUpdaterService;
    }

    /**
     * @param int $timestamp
     */
    public function run(int $timestamp): void
    {
        $constructions = $this->constructionRepository->getCompletedConstructions($timestamp);

        foreach ($constructions as $construction) {
            $worldRegion = $construction->getWorldRegion();

            if ($worldRegion->getPlayer()->getId() !== $construction->getPlayer()->getId()) {
                // Never process construction queue items for a region that no longer belongs to this player
                $this->constructionRepository->remove($construction);
                continue;
            }

            $this->processConstruction($construction);
        }
    }

    /**
     * @param Player $player
     * @param Construction $construction
     * @return Player
     */
    private function updatePlayerResources(Player $player, Construction $construction): Player
    {
        $upkeepCash = $construction->getNumber() * $construction->getGameUnit()->getUpkeepCash();
        $upkeepFood = $construction->getNumber() * $construction->getGameUnit()->getUpkeepFood();
        $upkeepWood = $construction->getNumber() * $construction->getGameUnit()->getUpkeepWood();
        $upkeepSteel = $construction->getNumber() * $construction->getGameUnit()->getUpkeepSteel();

        $incomeCash = $construction->getNumber() * $construction->getGameUnit()->getIncomeCash();
        $incomeFood = $construction->getNumber() * $construction->getGameUnit()->getIncomeFood();
        $incomeWood = $construction->getNumber() * $construction->getGameUnit()->getIncomeWood();
        $incomeSteel = $construction->getNumber() * $construction->getGameUnit()->getIncomeSteel();

        $resources = $player->getResources();
        $resources->setUpkeepCash($resources->getUpkeepCash() + $upkeepCash);
        $resources->setUpkeepFood($resources->getUpkeepFood() + $upkeepFood);
        $resources->setUpkeepWood($resources->getUpkeepWood() + $upkeepWood);
        $resources->setUpkeepSteel($resources->getUpkeepSteel() + $upkeepSteel);

        $resources->setIncomeCash($resources->getIncomeCash() + $incomeCash);
        $resources->setIncomeFood($resources->getIncomeFood() + $incomeFood);
        $resources->setIncomeWood($resources->getIncomeWood() + $incomeWood);
        $resources->setIncomeSteel($resources->getIncomeSteel() + $incomeSteel);

        $player->setResources($resources);

        return $player;
    }

    /**
     * @param Construction $construction
     */
    private function processConstruction(Construction $construction): void
    {
        // XXX TODO: Process income before processing construction...
        //$this->processPlayerIncome($construction->getPlayer(), $timestamp);

        $worldRegionUnit = $this->getWorldRegionUnit($construction);

        if ($worldRegionUnit !== null) {
            $worldRegionUnit->setAmount($construction->getNumber());
        } else {
            $worldRegionUnit = WorldRegionUnit::create($construction->getWorldRegion(), $construction->getGameUnit(), $construction->getNumber());
        }

        $player = $this->updatePlayerResources($construction->getPlayer(), $construction);
        $this->createConstructionReport($construction);

        $this->worldRegionUnitRepository->save($worldRegionUnit);
        $this->playerRepository->save($player);
        $this->constructionRepository->remove($construction);

        $this->networthUpdaterService->updateNetworthForPlayer($player);
    }

    /**
     * @param Construction $construction
     */
    private function createConstructionReport(Construction $construction): void
    {
        $reportType = Report::TYPE_GENERAL;
        if ($construction->getNumber() > 1) {
            $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getNameMulti()}!";
        } else {
            $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getName()}!";
        }

        $finishedConstructionTime = $construction->getTimestamp() + $construction->getGameUnit()->getTimestamp();
        $report = Report::createForPlayer($construction->getPlayer(), $finishedConstructionTime, $reportType, $message);
        $this->reportRepository->save($report);
    }

    /**
     * @param Construction $construction
     * @return WorldRegionUnit|null
     */
    private function getWorldRegionUnit(Construction $construction): ?WorldRegionUnit
    {
        $worldRegion = $construction->getWorldRegion();
        $worldRegionUnit = null;
        foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnitObject) {
            if ($worldRegionUnitObject->getGameUnit()->getId() == $construction->getGameUnit()->getId()) {
                $worldRegionUnit = $worldRegionUnitObject;
                break;
            }
        }

        return $worldRegionUnit;
    }
}
