<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class DestroyFood extends OperationProcessor
{
    protected const RESEARCH_DESTROY_FOOD_LEVEL_1 = 605;
    protected const RESEARCH_DESTROY_FOOD_LEVEL_2 = 606;
    protected const RESEARCH_DESTROY_FOOD_LEVEL_3 = 607;
    protected const RESEARCH_DESTROY_FOOD_LEVEL_4 = 608;
    protected const RESEARCH_DESTROY_FOOD_LEVEL_5 = 609;

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
        if ($this->hasResearched(self::RESEARCH_DESTROY_FOOD_LEVEL_1)) {
            $maxPercentage = 5;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_FOOD_LEVEL_2)) {
            $maxPercentage = 10;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_FOOD_LEVEL_3)) {
            $maxPercentage = 15;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_FOOD_LEVEL_4)) {
            $maxPercentage = 20;
        }

        if ($this->hasResearched(self::RESEARCH_DESTROY_FOOD_LEVEL_5)) {
            $maxPercentage = 25;
        }

        $random = mt_rand(1, $maxPercentage);
        $percentageDestroyed = round($random / 100);
        $player = $this->region->getPlayer();
        $foodDestroyed = intval($player->getResources()->getFood() * $percentageDestroyed);
        $player->getResources()->addFood(-$foodDestroyed);
        $this->playerRepository->save($player);

        $this->addToOperationLog("You destroyed {$percentageDestroyed}% of the food, {$foodDestroyed} in total!");
        $reportText = "{$this->playerRegion->getPlayer()->getName()} destroyed {$foodDestroyed} food on region {$this->region->getX()}, {$this->region->getY()}.";
        $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);
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

        $reportText = "{$this->playerRegion->getPlayer()->getName()} tried to destroy food on region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed to destroy food and lost {$specialOpsLost} Special Ops");
    }

    public function processPostOperation(): void
    {
        $player = $this->region->getPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
