<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class SniperAttack extends OperationProcessor
{
    protected const int GAME_UNIT_SOLDIER_ID = 300;
    protected const int GAME_UNIT_SNIPER_ID = 402;

    protected const int SOLDIERS_KILLED_PER_SNIPER = 5;

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
        $soldiers = 0;
        foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() == self::GAME_UNIT_SOLDIER_ID) {
                $soldiers = $soldiers + $worldRegionUnit->getAmount();
            }
        }

        if (($this->amount * self::SOLDIERS_KILLED_PER_SNIPER) > $soldiers) {
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getId() == self::GAME_UNIT_SOLDIER_ID) {
                    $this->worldRegionUnitRepository->remove($worldRegionUnit);
                    $this->addToOperationLog(
                        "You killed {$soldiers} {$worldRegionUnit->getGameUnit()->getNameMulti()}!"
                    );
                }
            }

            $this->addToOperationLog("You killed all soldiers!");
            $reportText = "Somebody launched a Sniper attack against region {$this->region->getX()}, {$this->region->getY()} and killed all soldiers.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        } else {
            $soldiersKilled = $this->amount * self::SOLDIERS_KILLED_PER_SNIPER;
            foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getId() == self::GAME_UNIT_SOLDIER_ID) {
                    $worldRegionUnit->setAmount($worldRegionUnit->getAmount() - $soldiersKilled);
                    $this->worldRegionUnitRepository->save($worldRegionUnit);
                    $this->addToOperationLog(
                        "You killed {$soldiersKilled} {$worldRegionUnit->getGameUnit()->getNameMulti()}!"
                    );
                }
            }

            $reportText = "Somebody launched a Sniper attack against region {$this->region->getX()}, {$this->region->getY()} and killed {$soldiersKilled} soldiers.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        }
    }

    public function processFailed(): void
    {
        $specialOpsLost = intval($this->getSpecialOps() * 0.05);
        $snipersLost = intval($this->amount * 0.2);

        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPECIAL_OPS_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $specialOpsLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }

            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SNIPER_ID) {
                $worldRegionUnit->setAmount(intval($worldRegionUnit->getAmount() - $snipersLost));
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
        }

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a Sniper attack against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog(
            "We failed our Sniper attack and lost {$specialOpsLost} Special Ops and {$snipersLost} Snipers"
        );
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
