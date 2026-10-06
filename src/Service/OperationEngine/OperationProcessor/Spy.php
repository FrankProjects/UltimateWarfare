<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class Spy extends OperationProcessor
{
    public function getFormula(): float
    {
        $spyLevel = $this->getAttackerResearchLevel('spy-technology');
        $counterEspionageLevel = $this->getTargetResearchLevel('counter-espionage');

        $probability = 0.50
            + 0.10 * ($spyLevel - $counterEspionageLevel)
            - $this->operation->getDifficulty();

        $probability = max(0.05, min(0.95, $probability));

        return $probability - mt_rand(0, 1000) / 1000.0;
    }

    public function processPreOperation(): void
    {
        // Do nothing
    }

    public function processSuccess(): void
    {
        /** @var array<int, array<int, array{name: string, amount: int}>> $unitsByCategory */
        $unitsByCategory = [];
        foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $resolvedUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            $categoryValue = $resolvedUnit->getGameUnitCategory()->value;
            $unitsByCategory[$categoryValue][] = [
                'name' => $resolvedUnit->getNameMulti(),
                'amount' => $worldRegionStackableUnit->getAmount(),
            ];
        }

        // Leveled buildings (Defense / Special) are reported by their level.
        foreach ($this->region->getWorldRegionLeveledUnits() as $leveledUnit) {
            $resolvedUnit = $this->gameUnitRegistry->find($leveledUnit->getGameUnit());
            $categoryValue = $resolvedUnit->getGameUnitCategory()->value;
            $unitsByCategory[$categoryValue][] = [
                'name' => $resolvedUnit->getName() . ' (level)',
                'amount' => $leveledUnit->getLevel(),
            ];
        }

        $alwaysShow = [
            GameUnitCategory::TROOPS,
            GameUnitCategory::BUILDINGS,
            GameUnitCategory::DEFENSE_BUILDINGS,
        ];
        $optional = [
            GameUnitCategory::SPECIAL_BUILDINGS,
            GameUnitCategory::NAVAL_UNITS,
            GameUnitCategory::AIR_UNITS,
            GameUnitCategory::MISSILES,
        ];

        foreach ($alwaysShow as $category) {
            $this->addSection($category->getLabel());
            $entries = $unitsByCategory[$category->value] ?? [];
            if (count($entries) === 0) {
                $this->addEmpty('No ' . strtolower($category->getLabel()) . ' detected in this region.');
                continue;
            }
            foreach ($entries as $entry) {
                $this->addRow($entry['name'], number_format($entry['amount'], 0, '.', ','));
            }
        }

        foreach ($optional as $category) {
            $entries = $unitsByCategory[$category->value] ?? [];
            if (count($entries) === 0) {
                continue;
            }
            $this->addSection($category->getLabel());
            foreach ($entries as $entry) {
                $this->addRow($entry['name'], number_format($entry['amount'], 0, '.', ','));
            }
        }
    }

    public function processFailed(): void
    {
        $reportText = "{$this->getPlayerRegionPlayer()->getName()} tried to spy"
            . " on region {$this->region->getX()}, {$this->region->getY()} but failed.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText, Report::TYPE_GENERAL);

        $this->addFailure("Our spies were caught. The enemy's defenses spotted us and an alert has been raised.");
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setGeneral(true);
        $this->playerRepository->save($player);
    }
}
