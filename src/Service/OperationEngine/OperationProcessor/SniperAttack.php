<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class SniperAttack extends OperationProcessor
{
    protected const int SOLDIERS_KILLED_PER_SNIPER = 5;
    private const float BASE_SUCCESS = 0.78;

    public function getFormula(): float
    {
        $rtLevel = $this->getAttackerResearchLevel('research-tier');
        $defNetLevel = $this->getTargetResearchLevel('defensive-network');

        $probability = self::BASE_SUCCESS
            + 0.05 * max(0, $rtLevel - 1)
            - 0.12 * $defNetLevel;

        $probability = max(0.05, min(0.95, $probability));

        return $probability - mt_rand(0, 1000) / 1000.0;
    }

    public function processPreOperation(): void
    {
        // Do nothing
    }

    public function processSuccess(): void
    {
        $soldiers = 0;
        foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::SOLDIER) {
                $soldiers = $soldiers + $worldRegionStackableUnit->getAmount();
            }
        }

        if (($this->amount * self::SOLDIERS_KILLED_PER_SNIPER) > $soldiers) {
            foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::SOLDIER) {
                    $this->worldRegionStackableUnitRepository->remove($worldRegionStackableUnit);
                    $unitName = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit())->getNameMulti();
                    $this->addToOperationLog("You killed {$soldiers} {$unitName}!");
                }
            }

            $this->addToOperationLog("You killed all soldiers!");
            $reportText = "Somebody launched a Sniper attack"
                . " against region {$this->region->getX()}, {$this->region->getY()} and killed all soldiers.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        } else {
            $soldiersKilled = $this->amount * self::SOLDIERS_KILLED_PER_SNIPER;
            foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::SOLDIER) {
                    $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() - $soldiersKilled);
                    $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
                    $unitName = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit())->getNameMulti();
                    $this->addToOperationLog("You killed {$soldiersKilled} {$unitName}!");
                }
            }

            $reportText = "Somebody launched a Sniper attack"
                . " against region {$this->region->getX()}, {$this->region->getY()}"
                . " and killed {$soldiersKilled} soldiers.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        }
    }

    public function processFailed(): void
    {
        $snipersLost = intval($this->amount * 0.05);

        foreach ($this->playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::SNIPER) {
                $worldRegionStackableUnit->setAmount(intval($worldRegionStackableUnit->getAmount() - $snipersLost));
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
            }
        }

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a Sniper attack"
            . " against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed our Sniper attack and lost {$snipersLost} Snipers");
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
