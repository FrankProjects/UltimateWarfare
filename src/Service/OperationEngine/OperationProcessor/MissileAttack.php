<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class MissileAttack extends OperationProcessor
{
    private const float BASE_SUCCESS = 0.62;

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
        foreach ($this->playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === $this->operation->getGameUnit()) {
                $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() - $this->amount);
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
            }
        }
    }

    public function processSuccess(): void
    {
        $totalBuildings = 0;
        foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            if ($gameUnit->getGameUnitCategory() === GameUnitCategory::BUILDINGS) {
                $totalBuildings = $totalBuildings + $worldRegionStackableUnit->getAmount();
            }
        }

        if (($this->amount / 2) > $totalBuildings) {
            $buildingsDestroyed = $totalBuildings;
            foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
                if ($gameUnit->getGameUnitCategory() === GameUnitCategory::BUILDINGS) {
                    $this->worldRegionStackableUnitRepository->remove($worldRegionStackableUnit);
                    $unitName = $gameUnit->getName();
                    $this->addToOperationLog("You destroyed all {$unitName} buildings!");
                }
            }

            $reportText = "{$this->getPlayerRegionPlayer()->getName()} launched a missile attack"
                . " against region {$this->region->getX()}, {$this->region->getY()} and destroyed all buildings.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        } else {
            $buildingsDestroyed = intval($this->amount / 2);
            foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
                if ($gameUnit->getGameUnitCategory() === GameUnitCategory::BUILDINGS) {
                    $percentage = $worldRegionStackableUnit->getAmount() / $totalBuildings;
                    $destroyed = intval($buildingsDestroyed * $percentage);
                    $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() - $destroyed);
                    $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
                    $unitName = $gameUnit->getName();
                    $this->addToOperationLog("You destroyed {$destroyed} {$unitName} buildings!");
                }
            }

            $reportText = "{$this->getPlayerRegionPlayer()->getName()} launched a missile attack"
                . " against region {$this->region->getX()}, {$this->region->getY()}"
                . " and destroyed {$buildingsDestroyed} buildings.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        }

        $this->addToOperationLog("You destroyed {$buildingsDestroyed} buildings!");
    }

    public function processFailed(): void
    {
        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a missile attack"
            . " against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog(
            "We failed our Missile Attack - the missiles were intercepted before reaching the target"
        );
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
