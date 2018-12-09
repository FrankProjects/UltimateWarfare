<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

final class ConstructionProcessor implements Processor
{
    /**
     * @var FederationRepository
     */
    private $federationRepository;

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
     * ConstructionProcessor constructor.
     *
     * @param FederationRepository $federationRepository
     * @param ConstructionRepository $constructionRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     */
    public function __construct(
        FederationRepository $federationRepository,
        ConstructionRepository $constructionRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->constructionRepository = $constructionRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
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

            // XXX TODO: Process income before updating income...
            //$this->processPlayerIncome($construction->getPlayer(), $timestamp);

            $gameUnitExist = false;
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getId() == $construction->getGameUnit()->getId()) {
                    $gameUnitExist = true;
                    break;
                }
            }


            if ($gameUnitExist) {
                $worldRegionUnit->setAmount($construction->getNumber());
            } else {
                $worldRegionUnit = WorldRegionUnit::create($worldRegion, $construction->getGameUnit(), $construction->getNumber());
            }

            $networth = $construction->getNumber() * $construction->getGameUnit()->getNetworth();

            $upkeepCash = $construction->getNumber() * $construction->getGameUnit()->getUpkeepCash();
            $upkeepFood = $construction->getNumber() * $construction->getGameUnit()->getUpkeepFood();
            $upkeepWood = $construction->getNumber() * $construction->getGameUnit()->getUpkeepWood();
            $upkeepSteel = $construction->getNumber() * $construction->getGameUnit()->getUpkeepSteel();

            $incomeCash = $construction->getNumber() * $construction->getGameUnit()->getIncomeCash();
            $incomeFood = $construction->getNumber() * $construction->getGameUnit()->getIncomeFood();
            $incomeWood = $construction->getNumber() * $construction->getGameUnit()->getIncomeWood();
            $incomeSteel = $construction->getNumber() * $construction->getGameUnit()->getIncomeSteel();

            $player = $construction->getPlayer();
            $player->setNetworth($player->getNetworth() + $networth);

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
            $federation = $player->getFederation();

            $reportType = 2;
            if ($construction->getNumber() > 1) {
                $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getNameMulti()}!";
            } else {
                $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getName()}!";
            }

            $finishedConstructionTime = $construction->getTimestamp() + $construction->getGameUnit()->getTimestamp();
            $report = Report::createForPlayer($player, $finishedConstructionTime, $reportType, $message);

            if ($federation !== null) {
                $federation->setNetworth($federation->getNetworth() + $networth);
                $this->federationRepository->save($federation);
            }

            $this->worldRegionUnitRepository->save($worldRegionUnit);
            $this->playerRepository->save($player);
            $this->reportRepository->save($report);
            $this->constructionRepository->remove($construction);
        }
    }
}
