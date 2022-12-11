<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class MissileAttack extends OperationProcessor
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
        $totalBuildings = 0;
        foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getGameUnitType()->getId() == GameUnitType::GAME_UNIT_TYPE_BUILDINGS) {
                $totalBuildings = $totalBuildings + $worldRegionUnit->getAmount();
            }
        }

        if (($this->amount / 2) > $totalBuildings) {
            $buildingsDestroyed = $totalBuildings;
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getGameUnitType()->getId() == GameUnitType::GAME_UNIT_TYPE_BUILDINGS) {
                    $this->worldRegionUnitRepository->remove($worldRegionUnit);
                    $this->addToOperationLog(
                        "You destroyed all {$worldRegionUnit->getGameUnit()->getName()} buildings!"
                    );
                }
            }

            $reportText = "{$this->playerRegion->getPlayer()->getName()} launched a missile attack against region {$this->region->getX()}, {$this->region->getY()} and destroyed all buildings.";
            $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);
        } else {
            $buildingsDestroyed = intval($this->amount / 2);
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getGameUnitType()->getId() == GameUnitType::GAME_UNIT_TYPE_BUILDINGS) {
                    $percentage = $worldRegionUnit->getAmount() / $totalBuildings;
                    $destroyed = intval($buildingsDestroyed * $percentage);
                    $worldRegionUnit->setAmount($worldRegionUnit->getAmount() - $destroyed);
                    $this->worldRegionUnitRepository->save($worldRegionUnit);
                    $this->addToOperationLog(
                        "You destroyed {$destroyed} {$worldRegionUnit->getGameUnit()->getName()} buildings!"
                    );
                }
            }

            $reportText = "{$this->playerRegion->getPlayer()->getName()} launched a missile attack against region {$this->region->getX()}, {$this->region->getY()} and destroyed {$buildingsDestroyed} buildings.";
            $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);
        }

        $this->addToOperationLog("You destroyed {$buildingsDestroyed} buildings!");
    }

    public function processFailed(): void
    {
        $troopsLost = intval($this->getSpecialOps() * 0.05);

        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPECIAL_OPS_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $troopsLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }

        $reportText = "{$this->playerRegion->getPlayer()->getName()} tried to launch a missile attack against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed our Missile Attack and lost {$troopsLost} Special Ops");
    }

    public function processPostOperation(): void
    {
        $player = $this->region->getPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
