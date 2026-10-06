<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class StrategicBomberAttack extends OperationProcessor
{
    protected const int BUILDINGS_DESTROYED_PER_BOMBER = 5;
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
        // Do nothing
    }

    public function processSuccess(): void
    {
        // Special buildings are leveled; their level represents how much building there is
        // to destroy. Strategic bomber damage is measured in levels.
        $totalLevels = 0;
        foreach ($this->getTargetSpecialBuildings() as $leveledUnit) {
            $totalLevels += $leveledUnit->getLevel();
        }

        $levelsToDestroy = $this->amount * self::BUILDINGS_DESTROYED_PER_BOMBER;

        if ($levelsToDestroy > $totalLevels) {
            foreach ($this->getTargetSpecialBuildings() as $leveledUnit) {
                $gameUnit = $this->gameUnitRegistry->find($leveledUnit->getGameUnit());
                $this->worldRegionLeveledUnitRepository->remove($leveledUnit);
                $this->addToOperationLog("You destroyed the {$gameUnit->getName()}!");
            }

            $this->addToOperationLog("You destroyed all special buildings!");
            $reportText = "Somebody launched a Strategic Bomber attack"
                . " against region {$this->region->getX()}, {$this->region->getY()}"
                . " and destroyed all special buildings.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        } else {
            foreach ($this->getTargetSpecialBuildings() as $leveledUnit) {
                $gameUnit = $this->gameUnitRegistry->find($leveledUnit->getGameUnit());
                $percentage = $leveledUnit->getLevel() / $totalLevels;
                $destroyed = (int) round($levelsToDestroy * $percentage);
                $newLevel = $leveledUnit->getLevel() - $destroyed;

                if ($newLevel <= 0) {
                    $this->worldRegionLeveledUnitRepository->remove($leveledUnit);
                    $this->addToOperationLog("You destroyed the {$gameUnit->getName()}!");
                } else {
                    $leveledUnit->setLevel($newLevel);
                    $leveledUnit->setHealth($gameUnit->getBattleStats()->getHealth() * $newLevel);
                    $this->worldRegionLeveledUnitRepository->save($leveledUnit);
                    $this->addToOperationLog("You destroyed {$destroyed} levels of {$gameUnit->getName()}!");
                }
            }

            $reportText = "Somebody launched a Strategic Bomber attack"
                . " against region {$this->region->getX()}, {$this->region->getY()}"
                . " and destroyed {$levelsToDestroy} building levels.";
            $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
        }
    }

    /**
     * @return array<int, \FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit>
     */
    private function getTargetSpecialBuildings(): array
    {
        $buildings = [];
        foreach ($this->region->getWorldRegionLeveledUnits() as $leveledUnit) {
            $gameUnit = $this->gameUnitRegistry->find($leveledUnit->getGameUnit());
            if ($gameUnit->getGameUnitCategory() === GameUnitCategory::SPECIAL_BUILDINGS) {
                $buildings[] = $leveledUnit;
            }
        }

        return $buildings;
    }

    public function processFailed(): void
    {
        $strategicBombersLost = intval($this->amount * 0.05);

        foreach ($this->playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === GameUnitEnum::STRATEGIC_BOMBER) {
                $worldRegionStackableUnit->setAmount(
                    intval($worldRegionStackableUnit->getAmount() - $strategicBombersLost)
                );
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
            }
        }

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a Strategic Bomber attack"
            . " against region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog(
            "We failed our Strategic Bomber attack and lost {$strategicBombersLost} Strategic Bombers"
        );
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
