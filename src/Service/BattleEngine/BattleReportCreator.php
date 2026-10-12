<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\BattleSummary;
use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;

final class BattleReportCreator
{
    private ReportRepository $reportRepository;
    private FederationNewsRepository $federationNewsRepository;

    public function __construct(
        ReportRepository $reportRepository,
        FederationNewsRepository $federationNewsRepository
    ) {
        $this->reportRepository = $reportRepository;
        $this->federationNewsRepository = $federationNewsRepository;
    }

    public function createBattleWonReports(Fleet $fleet, BattleSummary $battleSummary): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $attacker = $battleSummary->getAttacker();
        $defender = $battleSummary->getDefender();

        $reportString = "You took region {$targetWorldRegion->getRegionName()} from {$defender->getName()}";
        $this->createReport($attacker, $battleSummary, $reportString);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked"
            . " by {$attacker->getName()}, their forces were to big and we have lost the fight!";
        $this->createReport($defender, $battleSummary, $reportString);

        $defenderFederation = $defender->getFederation();
        if ($defenderFederation !== null) {
            $news = "{$defender->getName()} lost region {$targetWorldRegion->getRegionName()}"
                . " to {$attacker->getName()}";
            $this->createFederationNews($defenderFederation, $news);
        }

        $attackerFederation = $attacker->getFederation();
        if ($attackerFederation !== null) {
            $news = "{$attacker->getName()} took region {$targetWorldRegion->getRegionName()}"
                . " from {$defender->getName()}";
            $this->createFederationNews($attackerFederation, $news);
        }
    }

    public function createBattleLostReports(Fleet $fleet, BattleSummary $battleSummary): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        $attacker = $battleSummary->getAttacker();
        $defender = $battleSummary->getDefender();

        $reportString = "You attacked region {$targetWorldRegion->getRegionName()}"
            . " but the defending forces were too strong.";
        $this->createReport($attacker, $battleSummary, $reportString);

        $reportString = "Your region {$targetWorldRegion->getRegionName()} have been attacked"
            . " by {$attacker->getName()} and won the fight!";
        $this->createReport($defender, $battleSummary, $reportString);

        $defenderFederation = $defender->getFederation();
        if ($defenderFederation !== null) {
            $news = "{$defender->getName()} was attacked by {$attacker->getName()}"
                . " on region {$targetWorldRegion->getRegionName()} but the defending troops won the fight.";
            $this->createFederationNews($defenderFederation, $news);
        }

        $attackerFederation = $attacker->getFederation();
        if ($attackerFederation !== null) {
            $news = "{$attacker->getName()} attacked region {$targetWorldRegion->getRegionName()}"
                . " but the defender was too strong.";
            $this->createFederationNews($attackerFederation, $news);
        }
    }

    private function createFederationNews(Federation $federation, string $news): void
    {
        $federationNews = FederationNews::createForFederation($federation, $news);
        $this->federationNewsRepository->save($federationNews);
    }

    private function createReport(Player $player, BattleSummary $battleSummary, string $message): void
    {
        $report = Report::createForPlayer(
            $player,
            $battleSummary->getTimestamp(),
            Report::TYPE_ATTACKED,
            $message
        );
        $report->setBattleSummary($battleSummary);
        $this->reportRepository->save($report);
    }
}
