<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class AdvancedSpy extends OperationProcessor
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
        $player = $this->getTargetRegionPlayer();
        $resources = $player->getResources();

        $population = 0;
        foreach ($player->getWorldRegions() as $worldRegion) {
            $population += $worldRegion->getPopulation();
        }

        $this->addSection('Enemy Resources');
        $this->addRow('Cash', '$' . number_format($resources->getCash()));
        $this->addRow('Food', number_format($resources->getFood()));
        $this->addRow('Wood', number_format($resources->getWood()));
        $this->addRow('Steel', number_format($resources->getSteel()));
        $this->addRow('Population', number_format($population));

        $this->addSection('Recent Reports (last 24h)');
        $cutoff = time() - 86400;
        $reportsFound = 0;
        foreach ($player->getReports() as $report) {
            $timestamp = $report->getTimestamp();
            if ($timestamp > $cutoff && $timestamp < time()) {
                $this->addReportEntry($timestamp, $report->getReport());
                $reportsFound++;
            }
        }
        if ($reportsFound === 0) {
            $this->addEmpty('No reports filed in the last 24 hours.');
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
