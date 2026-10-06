<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class NuclearMissileAttack extends OperationProcessor
{
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
        foreach ($this->playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === $this->operation->getGameUnit()) {
                $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() - $this->amount);
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
            }
        }
    }

    public function processSuccess(): void
    {
        foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $this->worldRegionStackableUnitRepository->remove($worldRegionStackableUnit);
        }

        foreach ($this->region->getConstructions() as $construction) {
            $this->constructionRepository->remove($construction);
        }

        $this->region->setState(1);
        $this->region->setPlayer(null);
        $this->worldRegionRepository->save($this->region);

        $reportText = "{$this->getPlayerRegionPlayer()->getName()} launched a nuclear missile attack"
            . " against region {$this->region->getX()}, {$this->region->getY()} and destroyed everything.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog(
            "The region is fully destroyed, a high amount of toxic radiation"
            . " will make the region unliveable for an unknown amount of time!"
        );
    }

    public function processFailed(): void
    {
        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to launch a nuclear missile attack"
            . " on region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);

        $this->addToOperationLog(
            "Our nuclear missile attack failed - the missile was intercepted before reaching the target"
        );
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
