<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class AdvancedSpy2 extends OperationProcessor
{
    protected const int GAME_UNIT_SPY_ID = 407;

    public function getFormula(): float
    {
        $guards = $this->getGuards();
        $total_units = $this->amount + $guards + 1;

        return (3 * $this->amount / (2 * $total_units)) - (3 * $guards / (2 * $total_units)) - $this->operation->getDifficulty() + $this->getRandomChance();
    }

    public function processPreOperation(): void
    {
        // Do nothing
    }

    public function processSuccess(): void
    {
        $player = $this->region->getPlayer();
        $this->addToOperationLog("Searching for player reports...");

        foreach ($player->getReports() as $report) {
            if ($report->getTimestamp() < time() && $report->getTimestamp() > time() - 86400) {
                $this->addToOperationLog("Report - {$report->getTimestamp()}");
                $this->addToOperationLog("{$report->getReport()}");
            }
        }
    }

    public function processFailed(): void
    {
        $spiesLost = intval($this->amount * 0.05);

        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPY_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $spiesLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }

        $reportText = "{$this->playerRegion->getPlayer()->getName()} tried to spy on region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText, Report::TYPE_GENERAL);

        $this->addToOperationLog("We failed to spy and lost {$spiesLost} spies");
    }

    public function processPostOperation(): void
    {
        $player = $this->region->getPlayer();
        $player->getNotifications()->setGeneral(true);
        $this->playerRepository->save($player);
    }
}
