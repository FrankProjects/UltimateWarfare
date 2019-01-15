<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Fleet;
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
     */
    public function createBattleWonReports(Fleet $fleet): void
    {
        $timestamp = time();
        $targetWorldRegion = $fleet->getTargetWorldRegion();

        $reportString = "You took region {$targetWorldRegion->getRegionName()} from {$targetWorldRegion->getPlayer()->getName()}";
        $report = Report::createForPlayer($fleet->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
        $this->reportRepository->save($report);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked by {$fleet->getPlayer()->getName()}, their forces were to big and we have lost the fight!";
        $report = Report::createForPlayer($targetWorldRegion->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
        $this->reportRepository->save($report);

        if ($targetWorldRegion->getPlayer()->getFederation() !== null) {
            $reportString = "{$targetWorldRegion->getPlayer()->getName()} lost region {$targetWorldRegion->getRegionName()} to {$fleet->getPlayer()->getName()}";
            $report = Report::createForPlayer($targetWorldRegion->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
            $this->reportRepository->save($report);
        }

        if ($fleet->getPlayer()->getFederation() !== null) {
            $reportString = "{$fleet->getPlayer()->getName()} took region {$targetWorldRegion->getRegionName()} from {$targetWorldRegion->getPlayer()->getName()}";
            $report = Report::createForPlayer($fleet->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
            $this->reportRepository->save($report);
        }
    }

    /**
     * @param Fleet $fleet
     */
    public function createBattleLostReports(Fleet $fleet): void
    {
        $timestamp = time();
        $targetWorldRegion = $fleet->getTargetWorldRegion();

        $reportString = "You attacked region {$targetWorldRegion->getRegionName()} but the defending forces were too strong.";
        $report = Report::createForPlayer($fleet->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
        $this->reportRepository->save($report);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked by {$fleet->getPlayer()->getName()} and won the fight!";
        $report = Report::createForPlayer($targetWorldRegion->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
        $this->reportRepository->save($report);

        if ($targetWorldRegion->getPlayer()->getFederation() !== null) {
            $reportString = "{$targetWorldRegion->getPlayer()->getName()} was attacked by {$fleet->getPlayer()->getName()} on region {$targetWorldRegion->getRegionName()} but the defending troops won the fight.";
            $report = Report::createForPlayer($targetWorldRegion->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
            $this->reportRepository->save($report);
        }

        if ($fleet->getPlayer()->getFederation() !== null) {
            $reportString = "{$fleet->getPlayer()->getName()} attacked region {$targetWorldRegion->getRegionName()} but the defender was too strong.";
            $report = Report::createForPlayer($fleet->getPlayer(), $timestamp, Report::TYPE_ATTACKED, $reportString);
            $this->reportRepository->save($report);
        }
    }
}
