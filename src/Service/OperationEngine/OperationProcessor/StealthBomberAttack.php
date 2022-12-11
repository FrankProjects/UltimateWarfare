<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class StealthBomberAttack extends OperationProcessor
{
    protected const BUILDINGS_DESTROYED_PER_BOMBER = 5;
    protected const GAME_UNIT_STEALTH_BOMBER_ID = 404;

    public function getFormula(): float
    {
        $specialOps = $this->getSpecialOps();
        $guards = $this->getGuards();
        $total_units = $specialOps + $guards + 1;

        return (3 * $specialOps / (2 * $total_units)) - (3 * $guards / (2 * $total_units)) - $this->operation->getDifficulty(
            ) + $this->getRandomChance();
    }

    public function processPreOperation(): void
    {
        // Do nothing
    }

    public function processSuccess(): void
    {
        $totalBuildings = 0;
        foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getGameUnitType()->getId() == GameUnitType::GAME_UNIT_TYPE_SPECIAL_BUILDINGS) {
                $totalBuildings = $totalBuildings + $worldRegionUnit->getAmount();
            }
        }

        if (($this->amount * self::BUILDINGS_DESTROYED_PER_BOMBER) > $totalBuildings) {
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getGameUnitType()->getId() == GameUnitType::GAME_UNIT_TYPE_SPECIAL_BUILDINGS) {
                    $this->worldRegionUnitRepository->remove($worldRegionUnit);
                    $this->addToOperationLog("You destroyed all {$worldRegionUnit->getGameUnit()->getName()} buildings!");
                }
            }

            $this->addToOperationLog("You destroyed all special buildings!");
            $reportText = "Somebody launched a Stealth Bomber attack against region {$this->region->getX()}, {$this->region->getY()} and destroyed all special buildings.";
            $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);
        } else {
            $buildingsDestroyed = $this->amount * self::BUILDINGS_DESTROYED_PER_BOMBER;
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getGameUnitType()->getId(
                    ) == GameUnitType::GAME_UNIT_TYPE_SPECIAL_BUILDINGS) {
                    $percentage = $worldRegionUnit->getAmount() / $totalBuildings;
                    $destroyed = round($buildingsDestroyed * $percentage);
                    $worldRegionUnit->setAmount($worldRegionUnit->getAmount() - $destroyed);
                    $this->worldRegionUnitRepository->save($worldRegionUnit);
                    $this->addToOperationLog(
                        "You destroyed {$destroyed} {$worldRegionUnit->getGameUnit()->getName()} buildings!"
                    );
                }
            }

            $reportText = "Somebody launched a Stealth Bomber attack against region {$this->region->getX()}, {$this->region->getY()} and destroyed {$buildingsDestroyed} buildings.";
            $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);
        }
    }

    public function processFailed(): void
    {
        $specialOpsLost = intval($this->getSpecialOps() * 0.05);
        $stealthBombersLost = intval($this->amount * 0.1);

        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPECIAL_OPS_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $specialOpsLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }

            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_STEALTH_BOMBER_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $stealthBombersLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }

        $reportText = "{$this->playerRegion->getPlayer()->getName()} tried to launch a Stealth Bomber attack against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->region->getPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed our Stealth Bomber attack and lost {$specialOpsLost} Special Ops and {$stealthBombersLost} Stealth Bombers");
    }

    public function processPostOperation(): void
    {
        $player = $this->region->getPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}