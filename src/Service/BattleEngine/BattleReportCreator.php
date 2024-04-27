<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use RuntimeException;

final class BattleReportCreator
{
    private ReportRepository $reportRepository;

    public function __construct(
        ReportRepository $reportRepository
    ) {
        $this->reportRepository = $reportRepository;
    }

    public function createBattleWonReports(Fleet $fleet, int $timestamp): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $targetPlayer = $this->getWorldRegionPlayer($targetWorldRegion);

        $reportString = "You took region {$targetWorldRegion->getRegionName()} from {$targetPlayer->getName()}";
        $this->createReport($fleet->getPlayer(), $timestamp, $reportString);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked by {$fleet->getPlayer()->getName()}, their forces were to big and we have lost the fight!";
        $this->createReport($targetPlayer, $timestamp, $reportString);

        if ($targetPlayer->getFederation() !== null) {
            $reportString = "{$targetPlayer->getName()} lost region {$targetWorldRegion->getRegionName()} to {$fleet->getPlayer()->getName()}";
            $this->createReport($targetPlayer, $timestamp, $reportString);
        }

        if ($fleet->getPlayer()->getFederation() !== null) {
            $reportString = "{$fleet->getPlayer()->getName()} took region {$targetWorldRegion->getRegionName()} from {$targetPlayer->getName()}";
            $this->createReport($fleet->getPlayer(), $timestamp, $reportString);
        }
    }

    public function createBattleLostReports(Fleet $fleet, int $timestamp): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $targetPlayer = $this->getWorldRegionPlayer($targetWorldRegion);

        $reportString = "You attacked region {$targetWorldRegion->getRegionName()} but the defending forces were too strong.";
        $this->createReport($fleet->getPlayer(), $timestamp, $reportString);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked by {$fleet->getPlayer()->getName()} and won the fight!";
        $this->createReport($targetPlayer, $timestamp, $reportString);

        if ($targetPlayer->getFederation() !== null) {
            $reportString = "{$targetPlayer->getName()} was attacked by {$fleet->getPlayer()->getName()} on region {$targetWorldRegion->getRegionName()} but the defending troops won the fight.";
            $this->createReport($targetPlayer, $timestamp, $reportString);
        }

        if ($fleet->getPlayer()->getFederation() !== null) {
            $reportString = "{$fleet->getPlayer()->getName()} attacked region {$targetWorldRegion->getRegionName()} but the defender was too strong.";
            $this->createReport($fleet->getPlayer(), $timestamp, $reportString);
        }
    }

    private function createReport(Player $player, int $timestamp, string $report): void
    {
        $report = Report::createForPlayer($player, $timestamp, Report::TYPE_ATTACKED, $report);
        $this->reportRepository->save($report);
    }

    private function getWorldRegionPlayer(WorldRegion $worldRegion): Player
    {
        $player = $worldRegion->getPlayer();
        if ($player === null) {
            throw new RuntimeException("Region has no owner");
        }

        return $player;
    }
}
