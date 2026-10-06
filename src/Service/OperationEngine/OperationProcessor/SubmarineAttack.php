<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class SubmarineAttack extends OperationProcessor
{
    protected const int SHIPS_KILLED_PER_SUBMARINE = 1;
    private const float BASE_SUCCESS = 0.70;

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
        $ships = 0;
        foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::DESTROYER) {
                $ships = $ships + $worldRegionStackableUnit->getAmount();
            }
        }

        if (($this->amount * self::SHIPS_KILLED_PER_SUBMARINE) > $ships) {
            foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::DESTROYER) {
                    $this->worldRegionStackableUnitRepository->remove($worldRegionStackableUnit);
                    $unitName = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit())->getNameMulti();
                    $this->addToOperationLog("You sunk {$ships} {$unitName}!");
                }
            }

            $this->addToOperationLog("You sunk all ships!");
            $reportText = "Somebody launched a Submarine attack"
                . " against region {$this->region->getX()}, {$this->region->getY()} and sunk all ships.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        } else {
            $shipsDestroyed = $this->amount * self::SHIPS_KILLED_PER_SUBMARINE;
            foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::DESTROYER) {
                    $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() - $shipsDestroyed);
                    $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
                    $unitName = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit())->getNameMulti();
                    $this->addToOperationLog("You sunk {$shipsDestroyed} {$unitName}!");
                }
            }

            $reportText = "Somebody launched a Submarine attack"
                . " against region {$this->region->getX()}, {$this->region->getY()}"
                . " and sunk {$shipsDestroyed} ships.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        }
    }

    public function processFailed(): void
    {
        $submarinesLost = intval($this->amount * 0.05);

        foreach ($this->playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::SUBMARINE) {
                $worldRegionStackableUnit->setAmount(intval($worldRegionStackableUnit->getAmount() - $submarinesLost));
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
            }
        }

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a Submarine attack"
            . " against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog("We failed our Submarine attack and lost {$submarinesLost} Submarines");
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
