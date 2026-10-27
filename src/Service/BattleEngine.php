<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\BattleSummary;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\BattleSummaryRepository;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleLossCalculator;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleReinforcementService;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleReportCreator;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleResult;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleUpdaterService;
use FrankProjects\UltimateWarfare\Service\BattleEngine\Reinforcement;
use RuntimeException;

final class BattleEngine
{
    private BattleUpdaterService $battleUpdaterService;
    private BattleReportCreator $battleReportCreator;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private IncomeUpdaterService $incomeUpdaterService;
    private GameUnitRegistry $gameUnitRegistry;
    private BattleSummaryRepository $battleSummaryRepository;
    private PlayerStateUpdaterService $playerStateUpdaterService;
    private EntityLockRepository $entityLockRepository;
    private BattleReinforcementService $battleReinforcementService;
    private MoraleUpdaterService $moraleUpdaterService;

    public function __construct(
        BattleUpdaterService $battleUpdaterService,
        BattleReportCreator $battleReportCreator,
        NetWorthUpdaterService $netWorthUpdaterService,
        IncomeUpdaterService $incomeUpdaterService,
        GameUnitRegistry $gameUnitRegistry,
        BattleSummaryRepository $battleSummaryRepository,
        PlayerStateUpdaterService $playerStateUpdaterService,
        EntityLockRepository $entityLockRepository,
        BattleReinforcementService $battleReinforcementService,
        MoraleUpdaterService $moraleUpdaterService
    ) {
        $this->battleUpdaterService = $battleUpdaterService;
        $this->battleReportCreator = $battleReportCreator;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->incomeUpdaterService = $incomeUpdaterService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->battleSummaryRepository = $battleSummaryRepository;
        $this->playerStateUpdaterService = $playerStateUpdaterService;
        $this->entityLockRepository = $entityLockRepository;
        $this->battleReinforcementService = $battleReinforcementService;
        $this->moraleUpdaterService = $moraleUpdaterService;
    }

    /**
     * Neighbouring regions of the defender send reinforcements through their Train Station, Airfield and Harbor.
     * The battle result includes the units each side lost.
     *
     * @param Fleet $fleet
     * @return BattleResult
     */
    public function battle(Fleet $fleet): BattleResult
    {
        // The attacker is locked for the whole request; settle and lock the defender as well
        $defendingPlayer = $fleet->getTargetWorldRegion()->getPlayer();
        if ($defendingPlayer !== null) {
            $this->playerStateUpdaterService->updateStateForPlayer($defendingPlayer, time());
        }

        // A concurrent request may have used this fleet, or taken the region after we locked its owner
        if (!$this->entityLockRepository->lockForUpdate($fleet)) {
            throw new RuntimeException('Fleet does not exist!');
        }
        $targetWorldRegion = $fleet->getTargetWorldRegion();
        if (
            !$this->entityLockRepository->lockForUpdate($targetWorldRegion)
            || $targetWorldRegion->getPlayer() !== $defendingPlayer
        ) {
            throw new RuntimeException('The target region changed owner, please try again.');
        }

        $this->ensureCanAttack($fleet);

        $now = time();
        $attackerGameUnits = $fleet->getFleetUnits()->toArray();
        $regionDefenderGameUnits = $targetWorldRegion->getWorldRegionStackableUnits()->toArray();
        // Defenders are both stackable units and leveled buildings present in the region.
        $defenderGameUnits = array_merge(
            $regionDefenderGameUnits,
            $targetWorldRegion->getWorldRegionLeveledUnits()->toArray()
        );
        $defenderDamageMultiplier = $this->getDefenderDamageMultiplier(
            $fleet->getTargetWorldRegion()->getPlayer()
        );

        $reinforcements = $defendingPlayer !== null
            ? $this->battleReinforcementService->gatherReinforcements($targetWorldRegion, $defendingPlayer)
            : [];
        $reinforcementGameUnits = array_map(
            fn (Reinforcement $reinforcement): WorldRegionStackableUnit => $reinforcement->getBattleUnit(),
            $reinforcements
        );
        $defenderGameUnits = [...$defenderGameUnits, ...$reinforcementGameUnits];

        $battleLossCalculator = new BattleLossCalculator(
            $attackerGameUnits,
            $defenderGameUnits,
            $reinforcementGameUnits
        );

        // Battle phases change the amounts in place, so count the crews before they start
        $attackerStartCrew = $this->moraleUpdaterService->getCrew($attackerGameUnits);
        $regionDefenderStartCrew = $this->moraleUpdaterService->getCrew($regionDefenderGameUnits);
        $strengthMultipliers = $this->getStrengthMultipliers(
            $fleet,
            $attackerGameUnits,
            $regionDefenderGameUnits,
            $reinforcements,
            $now
        );

        $battlePhaseResults = [];
        foreach ($this->getBattlePhases() as $battlePhaseName) {
            $battlePhase = BattlePhase::factory(
                $battlePhaseName,
                $attackerGameUnits,
                $defenderGameUnits,
                $this->gameUnitRegistry,
                $defenderDamageMultiplier,
                $strengthMultipliers
            );
            $battlePhase->startBattlePhase();

            $attackerGameUnits = $battlePhase->getAttackerGameUnits();
            $defenderGameUnits = $battlePhase->getDefenderGameUnits();

            $battlePhaseResults[] = $battlePhase;
        }

        $battleResults = new BattleResult(
            $battlePhaseResults,
            [
                ...$this->getMoraleBattleLog($fleet, $regionDefenderGameUnits, $now),
                ...$this->battleReinforcementService->getBattleLog($reinforcements),
                ...$this->getReinforcementMoraleBattleLog($reinforcements, $now),
            ]
        );
        $battleResults->setLostUnits(
            $battleLossCalculator->calculate($attackerGameUnits, $defenderGameUnits, $battleResults->hasWon())
        );

        // Before processing the results, as the surviving attackers take their morale into the region they join
        $this->moraleUpdaterService->applyAttackerLoss(
            $fleet,
            $attackerStartCrew,
            $attackerStartCrew - $this->moraleUpdaterService->getCrew($attackerGameUnits),
            $battleResults->hasWon()
        );
        if (!$battleResults->hasWon()) {
            $survivingRegionDefenders = array_filter(
                $regionDefenderGameUnits,
                fn (WorldRegionStackableUnit $stackableUnit): bool => in_array($stackableUnit, $defenderGameUnits, true)
            );
            $this->moraleUpdaterService->applyDefenderLoss(
                $targetWorldRegion,
                $regionDefenderStartCrew,
                $regionDefenderStartCrew - $this->moraleUpdaterService->getCrew($survivingRegionDefenders),
                $now
            );
        }
        $this->applyReinforcementMoraleLoss($reinforcements, $defenderGameUnits, $now);

        $this->processResults($battleResults, $fleet, $attackerGameUnits, $defenderGameUnits, $reinforcements);

        return $battleResults;
    }

    /**
     * Each unit group fights with the morale of the fleet or region it belongs to; reinforcements with that of
     * the region they come from
     *
     * @param FleetUnit[] $attackerGameUnits
     * @param WorldRegionStackableUnit[] $regionDefenderGameUnits
     * @param Reinforcement[] $reinforcements
     *
     * @return array<int, float> Keyed by object id
     */
    private function getStrengthMultipliers(
        Fleet $fleet,
        array $attackerGameUnits,
        array $regionDefenderGameUnits,
        array $reinforcements,
        int $now
    ): array {
        $strengthMultipliers = [];
        $attackerMultiplier = $this->moraleUpdaterService->getStrengthMultiplier($fleet->getMorale());
        foreach ($attackerGameUnits as $fleetUnit) {
            $strengthMultipliers[spl_object_id($fleetUnit)] = $attackerMultiplier;
        }

        $defenderMultiplier = $this->moraleUpdaterService->getStrengthMultiplier(
            $this->moraleUpdaterService->project($fleet->getTargetWorldRegion(), $now)
        );
        foreach ($regionDefenderGameUnits as $stackableUnit) {
            $strengthMultipliers[spl_object_id($stackableUnit)] = $defenderMultiplier;
        }

        foreach ($reinforcements as $reinforcement) {
            $strengthMultipliers[spl_object_id($reinforcement->getBattleUnit())] =
                $this->moraleUpdaterService->getStrengthMultiplier(
                    $this->moraleUpdaterService->project($reinforcement->getSourceUnit()->getWorldRegion(), $now)
                );
        }

        return $strengthMultipliers;
    }

    /**
     * E.g. "Attacking units fight at 72% morale (86% strength)"
     *
     * @param WorldRegionStackableUnit[] $regionDefenderGameUnits
     *
     * @return array<string>
     */
    private function getMoraleBattleLog(Fleet $fleet, array $regionDefenderGameUnits, int $now): array
    {
        $battleLog = [$this->getMoraleLogLine('Attacking units', $fleet->getMorale())];
        if ($regionDefenderGameUnits !== []) {
            $battleLog[] = $this->getMoraleLogLine(
                'Defending units',
                $this->moraleUpdaterService->project($fleet->getTargetWorldRegion(), $now)
            );
        }

        return $battleLog;
    }

    /**
     * One line per region that sent reinforcements
     *
     * @param Reinforcement[] $reinforcements
     *
     * @return array<string>
     */
    private function getReinforcementMoraleBattleLog(array $reinforcements, int $now): array
    {
        $battleLog = [];
        foreach ($reinforcements as $reinforcement) {
            $worldRegion = $reinforcement->getSourceUnit()->getWorldRegion();
            $battleLog[spl_object_id($worldRegion)] = $this->getMoraleLogLine(
                "Reinforcements from region {$worldRegion->getRegionName()}",
                $this->moraleUpdaterService->project($worldRegion, $now)
            );
        }

        return array_values($battleLog);
    }

    private function getMoraleLogLine(string $units, int $morale): string
    {
        $strength = (int) round($this->moraleUpdaterService->getStrengthMultiplier($morale) * 100);

        return "{$units} fight at {$morale}% morale ({$strength}% strength)";
    }

    /**
     * Regions that sent reinforcements lose morale for the share of the units they sent that died
     *
     * @param Reinforcement[] $reinforcements
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $survivingDefenderGameUnits
     */
    private function applyReinforcementMoraleLoss(
        array $reinforcements,
        array $survivingDefenderGameUnits,
        int $now
    ): void {
        $crews = [];
        foreach ($reinforcements as $reinforcement) {
            $battleUnit = $reinforcement->getBattleUnit();
            $worldRegion = $reinforcement->getSourceUnit()->getWorldRegion();
            $surviving = in_array($battleUnit, $survivingDefenderGameUnits, true) ? $battleUnit->getAmount() : 0;

            $key = spl_object_id($worldRegion);
            $crews[$key]['region'] = $worldRegion;
            $crews[$key]['start'] = ($crews[$key]['start'] ?? 0)
                + $this->moraleUpdaterService->getUnitCrew($battleUnit->getGameUnit(), $reinforcement->getAmount());
            $crews[$key]['lost'] = ($crews[$key]['lost'] ?? 0)
                + $this->moraleUpdaterService->getUnitCrew(
                    $battleUnit->getGameUnit(),
                    $reinforcement->getAmount() - $surviving
                );
        }

        foreach ($crews as $crew) {
            $this->moraleUpdaterService->applyDefenderLoss($crew['region'], $crew['start'], $crew['lost'], $now);
        }
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

        return $level === 0 ? 1.0 : pow(0.9, $level);
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
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits Including reinforcements
     * @param Reinforcement[] $reinforcements
     */
    private function processResults(
        BattleResult $battleResults,
        Fleet $fleet,
        array $attackerGameUnits,
        array $defenderGameUnits,
        array $reinforcements
    ): void {
        $defendingPlayer = $fleet->getTargetWorldRegion()->getPlayer();
        if ($defendingPlayer === null) {
            throw new RuntimeException("Target region has no owner");
        }

        // Created before updating the battle outcome, as a won battle transfers the region to the attacker
        $battleSummary = BattleSummary::create(
            $fleet->getPlayer()->getWorld(),
            $fleet->getPlayer(),
            $defendingPlayer,
            $battleResults->getBattleLog(),
            $battleResults->hasWon(),
            time(),
            $battleResults->getLostUnits() ?? []
        );
        $this->battleSummaryRepository->save($battleSummary);

        if ($battleResults->hasWon()) {
            $this->battleUpdaterService->updateBattleWon($fleet, $attackerGameUnits);
            $this->battleReportCreator->createBattleWonReports($fleet, $battleSummary);
        } else {
            // Reinforcements only fought here; they must not be saved into the target region
            $reinforcementGameUnits = array_map(
                fn (Reinforcement $reinforcement): WorldRegionStackableUnit => $reinforcement->getBattleUnit(),
                $reinforcements
            );
            $regionDefenderGameUnits = array_filter(
                $defenderGameUnits,
                fn (WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): bool
                    => !in_array($defenderGameUnit, $reinforcementGameUnits, true)
            );
            $this->battleUpdaterService->updateBattleLost($fleet, $attackerGameUnits, $regionDefenderGameUnits);
            $this->battleReportCreator->createBattleLostReports($fleet, $battleSummary);
        }

        $this->battleReinforcementService->applyLosses($reinforcements, $defenderGameUnits);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($fleet->getPlayer());
        $this->netWorthUpdaterService->updateNetWorthForPlayer($defendingPlayer);

        $this->incomeUpdaterService->updateIncomeForPlayer($fleet->getPlayer());
        $this->incomeUpdaterService->updateIncomeForPlayer($defendingPlayer);
    }
}
