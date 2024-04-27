<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class ChemicalMissileAttack extends OperationProcessor
{
    public function getFormula(): float
    {
        $specialOps = $this->getSpecialOps();
        $guards = $this->getGuards();
        $total_units = $specialOps + $guards + 1;

        return (3 * $specialOps / (2 * $total_units)) - (3 * $guards / (2 * $total_units)) - $this->operation->getDifficulty() + $this->getRandomChance();
    }

    public function processPreOperation(): void
    {
        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === $this->operation->getGameUnit()->getId()) {
                $worldRegionUnit->setAmount($worldRegionUnit->getAmount() - $this->amount);
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }
    }

    public function processSuccess(): void
    {
        if ($this->amount * 100 > $this->region->getPopulation()) {
            $this->region->setPopulation(0);

            $reportText = "{$this->getPlayerRegionPlayer()->getName()} launched a chemical missile attack against region {$this->region->getX()}, {$this->region->getY()} and killed all population.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

            $this->addToOperationLog("You killed all population!");
        } else {
            $populationKilled = $this->amount * 100;
            $this->region->setPopulation($this->region->getPopulation() - $populationKilled);

            $reportText = "{$this->getPlayerRegionPlayer()->getName()} launched a chemical missile attack against region {$this->region->getX()}, {$this->region->getY()} and killed {$populationKilled} population.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

            $this->addToOperationLog("You killed {$populationKilled} population!");
        }

        $this->worldRegionRepository->save($this->region);
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

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a chemical missile attack on region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed to our chemical missile attack and lost {$specialOpsLost} Special Ops");
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
