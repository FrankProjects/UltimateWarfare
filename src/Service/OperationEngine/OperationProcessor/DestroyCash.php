<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class DestroyCash extends OperationProcessor
{
    protected const int RESEARCH_DESTROY_CASH_LEVEL_1 = 600;
    protected const int RESEARCH_DESTROY_CASH_LEVEL_2 = 601;
    protected const int RESEARCH_DESTROY_CASH_LEVEL_3 = 602;
    protected const int RESEARCH_DESTROY_CASH_LEVEL_4 = 603;
    protected const int RESEARCH_DESTROY_CASH_LEVEL_5 = 604;


    public function getFormula(): float
    {
        $specialOps = $this->getSpecialOps();
        $guards = $this->getGuards();
        $total_units = $specialOps + $guards + 1;

        return (3 * $specialOps / (2 * $total_units)) - (3 * $guards / (2 * $total_units)) - $this->operation->getDifficulty() + $this->getRandomChance();
    }

    public function processPreOperation(): void
    {
        // Do nothing
    }

    public function processSuccess(): void
    {
        $maxPercentage = 0;
        if ($this->hasResearched(self::RESEARCH_DESTROY_CASH_LEVEL_1)) {
            $maxPercentage = 2;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_CASH_LEVEL_2)) {
            $maxPercentage = 4;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_CASH_LEVEL_3)) {
            $maxPercentage = 6;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_CASH_LEVEL_4)) {
            $maxPercentage = 8;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_CASH_LEVEL_5)) {
            $maxPercentage = 10;
        }

        $random = mt_rand(1, $maxPercentage);
        $percentageDestroyed = round($random / 100);
        $player = $this->getTargetRegionPlayer();
        $cashDestroyed = intval($player->getResources()->getCash() * $percentageDestroyed);
        $player->getResources()->addCash(-$cashDestroyed);
        $this->playerRepository->save($player);

        $this->addToOperationLog("You destroyed {$percentageDestroyed}% of the cash, {$cashDestroyed} in total!");
        $reportText = "{$this->getPlayerRegionPlayer()->getName()} destroyed {$cashDestroyed} cash on region {$this->region->getX()}, {$this->region->getY()}.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
    }

    public function processFailed(): void
    {
        $specialOpsLost = intval($this->getSpecialOps() * 0.05);

        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPECIAL_OPS_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $specialOpsLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to destroy cash on region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed to destroy cash and lost {$specialOpsLost} Special Ops");
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
