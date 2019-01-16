<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;

final class BattleReportCreator
{
    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * BattleReportCreator constructor.
     *
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        ReportRepository $reportRepository
    ) {
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param Fleet $fleet
     * @param int $timestamp
     */
    public function createBattleWonReports(Fleet $fleet, int $timestamp): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();

        $reportString = "You took region {$targetWorldRegion->getRegionName()} from {$targetWorldRegion->getPlayer()->getName()}";
        $this->createReport($fleet->getPlayer(), $timestamp, $reportString);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked by {$fleet->getPlayer()->getName()}, their forces were to big and we have lost the fight!";
        $this->createReport($targetWorldRegion->getPlayer(), $timestamp, $reportString);

        if ($targetWorldRegion->getPlayer()->getFederation() !== null) {
            $reportString = "{$targetWorldRegion->getPlayer()->getName()} lost region {$targetWorldRegion->getRegionName()} to {$fleet->getPlayer()->getName()}";
            $this->createReport($targetWorldRegion->getPlayer(), $timestamp, $reportString);
        }

        if ($fleet->getPlayer()->getFederation() !== null) {
            $reportString = "{$fleet->getPlayer()->getName()} took region {$targetWorldRegion->getRegionName()} from {$targetWorldRegion->getPlayer()->getName()}";
            $this->createReport($fleet->getPlayer(), $timestamp, $reportString);
        }
    }

    /**
     * @param Fleet $fleet
     * @param int $timestamp
     */
    public function createBattleLostReports(Fleet $fleet, int $timestamp): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();

        $reportString = "You attacked region {$targetWorldRegion->getRegionName()} but the defending forces were too strong.";
        $this->createReport($fleet->getPlayer(), $timestamp, $reportString);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked by {$fleet->getPlayer()->getName()} and won the fight!";
        $this->createReport($targetWorldRegion->getPlayer(), $timestamp, $reportString);

        if ($targetWorldRegion->getPlayer()->getFederation() !== null) {
            $reportString = "{$targetWorldRegion->getPlayer()->getName()} was attacked by {$fleet->getPlayer()->getName()} on region {$targetWorldRegion->getRegionName()} but the defending troops won the fight.";
            $this->createReport($targetWorldRegion->getPlayer(), $timestamp, $reportString);
        }

        if ($fleet->getPlayer()->getFederation() !== null) {
            $reportString = "{$fleet->getPlayer()->getName()} attacked region {$targetWorldRegion->getRegionName()} but the defender was too strong.";
            $this->createReport($fleet->getPlayer(), $timestamp, $reportString);
        }
    }

    /**
     * @param Player $player
     * @param int $timestamp
     * @param string $report
     */
    private function createReport(Player $player, int $timestamp, string $report): void
    {
        $report = Report::createForPlayer($player, $timestamp, Report::TYPE_ATTACKED, $report);
        $this->reportRepository->save($report);
    }
}
