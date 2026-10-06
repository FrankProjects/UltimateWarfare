<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleReportCreator;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleResult;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleUpdaterService;
use RuntimeException;

final class BattleEngine
{
    private BattleUpdaterService $battleUpdaterService;
    private BattleReportCreator $battleReportCreator;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private IncomeUpdaterService $incomeUpdaterService;
    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(
        BattleUpdaterService $battleUpdaterService,
        BattleReportCreator $battleReportCreator,
        NetWorthUpdaterService $netWorthUpdaterService,
        IncomeUpdaterService $incomeUpdaterService,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->battleUpdaterService = $battleUpdaterService;
        $this->battleReportCreator = $battleReportCreator;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->incomeUpdaterService = $incomeUpdaterService;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    /**
     * XXX TODO: Add train station/airfield/harbor support
     * XXX TODO: Add battle report
     * XXX TODO: Add battle "lost units" summary
     *
     * @param Fleet $fleet
     * @return BattleResult
     */
    public function battle(Fleet $fleet): BattleResult
    {
        $this->ensureCanAttack($fleet);

        $attackerGameUnits = $fleet->getFleetUnits()->toArray();
        // Defenders are both stackable units and leveled buildings present in the region.
        $defenderGameUnits = array_merge(
            $fleet->getTargetWorldRegion()->getWorldRegionStackableUnits()->toArray(),
            $fleet->getTargetWorldRegion()->getWorldRegionLeveledUnits()->toArray()
        );
        $defenderDamageMultiplier = $this->getDefenderDamageMultiplier(
            $fleet->getTargetWorldRegion()->getPlayer()
        );

        $battlePhaseResults = [];
        foreach ($this->getBattlePhases() as $battlePhaseName) {
            $battlePhase = BattlePhase::factory(
                $battlePhaseName,
                $attackerGameUnits,
                $defenderGameUnits,
                $this->gameUnitRegistry,
                $defenderDamageMultiplier
            );
            $battlePhase->startBattlePhase();

            $attackerGameUnits = $battlePhase->getAttackerGameUnits();
            $defenderGameUnits = $battlePhase->getDefenderGameUnits();

            $battlePhaseResults[] = $battlePhase;
        }

        $battleResults = new BattleResult($battlePhaseResults);

        $this->processResults($battleResults, $fleet, $attackerGameUnits, $defenderGameUnits);

        return $battleResults;
    }

    /**
     * @return array<int, string>
     */
    private function getBattlePhases(): array
    {
        return [
            BattlePhase::AIR_PHASE,
            BattlePhase::SEA_PHASE,
            BattlePhase::GROUND_PHASE
        ];
    }

    private function getDefenderDamageMultiplier(?Player $defender): float
    {
        if ($defender === null) {
            return 1.0;
        }

        $level = 0;
        foreach ($defender->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() !== true) {
                continue;
            }
            if ($playerResearch->getResearchSlug() !== 'defensive-network') {
                continue;
            }
            if ($playerResearch->getLevel() > $level) {
                $level = $playerResearch->getLevel();
            }
        }

        return $level === 0 ? 1.0 : pow(0.75, $level);
    }

    private function ensureCanAttack(Fleet $fleet): void
    {
        if ($fleet->getTimestampArrive() > time()) {
            throw new RuntimeException("Fleet not arrived yet");
        }

        $targetPlayer = $fleet->getTargetWorldRegion()->getPlayer();
        if ($targetPlayer === null) {
            throw new RuntimeException("Target region has no owner");
        }

        if ($fleet->getPlayer()->getId() === $targetPlayer->getId()) {
            throw new RuntimeException("You can not attack yourself");
        }

        if (count($targetPlayer->getWorldRegions()) === 1) {
            throw new RuntimeException("Target player has only 1 region left");
        }

        if ($targetPlayer->getTimestampJoined() + 172800 > time()) {
            throw new RuntimeException("You can not attack this player in the first 48 hours");
        }
    }

    /**
     * @param array<FleetUnit> $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    private function processResults(
        BattleResult $battleResults,
        Fleet $fleet,
        array $attackerGameUnits,
        array $defenderGameUnits
    ): void {
        $defendingPlayer = $fleet->getTargetWorldRegion()->getPlayer();
        if ($defendingPlayer === null) {
            throw new RuntimeException("Target region has no owner");
        }

        $timestamp = time();

        if ($battleResults->hasWon()) {
            $this->battleUpdaterService->updateBattleWon($fleet, $attackerGameUnits);
            $this->battleReportCreator->createBattleWonReports($fleet, $timestamp);
        } else {
            $this->battleUpdaterService->updateBattleLost($fleet, $attackerGameUnits, $defenderGameUnits);
            $this->battleReportCreator->createBattleLostReports($fleet, $timestamp);
        }

        $this->netWorthUpdaterService->updateNetWorthForPlayer($fleet->getPlayer());
        $this->netWorthUpdaterService->updateNetWorthForPlayer($defendingPlayer);

        $this->incomeUpdaterService->updateIncomeForPlayer($fleet->getPlayer());
        $this->incomeUpdaterService->updateIncomeForPlayer($defendingPlayer);
    }
}
