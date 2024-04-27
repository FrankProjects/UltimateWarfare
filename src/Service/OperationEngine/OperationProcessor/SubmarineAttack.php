<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class SubmarineAttack extends OperationProcessor
{
    protected const int GAME_UNIT_SUBMARINE_ID = 403;
    protected const int GAME_UNIT_SHIP_ID = 303;
    protected const int SHIPS_KILLED_PER_SUBMARINE = 1;

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
        $ships = 0;
        foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() == self::GAME_UNIT_SHIP_ID) {
                $ships = $ships + $worldRegionUnit->getAmount();
            }
        }

        if (($this->amount * self::SHIPS_KILLED_PER_SUBMARINE) > $ships) {
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getId() == self::GAME_UNIT_SHIP_ID) {
                    $this->worldRegionUnitRepository->remove($worldRegionUnit);
                    $this->addToOperationLog("You sunk {$ships} {$worldRegionUnit->getGameUnit()->getNameMulti()}!");
                }
            }

            $this->addToOperationLog("You sunk all ships!");
            $reportText = "Somebody launched a Submarine attack against region {$this->region->getX()}, {$this->region->getY()} and sunk all ships.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        } else {
            $shipsDestroyed = $this->amount * self::SHIPS_KILLED_PER_SUBMARINE;
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getId() == self::GAME_UNIT_SHIP_ID) {
                    $worldRegionUnit->setAmount($worldRegionUnit->getAmount() - $shipsDestroyed);
                    $this->worldRegionUnitRepository->save($worldRegionUnit);
                    $this->addToOperationLog(
                        "You sunk {$shipsDestroyed} {$worldRegionUnit->getGameUnit()->getNameMulti()}!"
                    );
                }
            }

            $reportText = "Somebody launched a Submarine attack against region {$this->region->getX()}, {$this->region->getY()} and sunk {$shipsDestroyed} ships.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        }
    }

    public function processFailed(): void
    {
        $specialOpsLost = intval($this->getSpecialOps() * 0.05);
        $submarinesLost = intval($this->amount * 0.2);

        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPECIAL_OPS_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $specialOpsLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }

            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SUBMARINE_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $submarinesLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a Submarine attack against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog(
            "We failed our Submarine attack and lost {$specialOpsLost} Special Ops and {$submarinesLost} Submarines"
        );
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
