<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattleResult;
use RuntimeException;

final class BattleEngine
{
    /**
     * @var FleetRepository
     */
    private $fleetRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var FleetUnitRepository
     */
    private $fleetUnitRepository;

    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var WorldRegionUnitRepository
     */
    private $worldRegionUnitRepository;

    /**
     * @var NetworthUpdaterService
     */
    private $networthUpdaterService;

    /**
     * @var IncomeUpdaterService
     */
    private $incomeUpdaterService;

    /**
     * BattleEngine constructor.
     *
     * @param FleetRepository $fleetRepository
     * @param ReportRepository $reportRepository
     * @param FleetUnitRepository $fleetUnitRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     * @param NetworthUpdaterService $networthUpdaterService
     * @param IncomeUpdaterService $incomeUpdaterService
     */
    public function __construct(
        FleetRepository $fleetRepository,
        ReportRepository $reportRepository,
        FleetUnitRepository $fleetUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository,
        NetworthUpdaterService $networthUpdaterService,
        IncomeUpdaterService $incomeUpdaterService
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->reportRepository = $reportRepository;
        $this->fleetUnitRepository = $fleetUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
        $this->networthUpdaterService = $networthUpdaterService;
        $this->incomeUpdaterService = $incomeUpdaterService;
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
        $defenderGameUnits = $fleet->getTargetWorldRegion()->getWorldRegionUnits()->toArray();

        $battlePhaseResults = [];
        foreach ($this->getBattlePhases() as $battlePhaseName) {
            $battlePhase = BattlePhase::factory($battlePhaseName, $attackerGameUnits, $defenderGameUnits);
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
     * @return array
     */
    private function getBattlePhases(): array
    {
        return [
            BattlePhase::AIR_PHASE,
            BattlePhase::SEA_PHASE,
            BattlePhase::GROUND_PHASE
        ];
    }

    /**
     * @param Fleet $fleet
     */
    private function ensureCanAttack(Fleet $fleet): void
    {
        if ($fleet->getTimestampArrive() > time()) {
            throw new RunTimeException("Fleet not arrived yet");
        }

        $targetPlayer = $fleet->getTargetWorldRegion()->getPlayer();
        if ($targetPlayer === null) {
            throw new RunTimeException("Target region has no owner");
        }

        if ($fleet->getPlayer()->getId() === $targetPlayer->getId()) {
            throw new RunTimeException("You can not attack yourself");
        }

        if (count($targetPlayer->getWorldRegions()) === 1) {
            throw new RunTimeException("Target player has only 1 region left");
        }

        if ($targetPlayer->getTimestampJoined() + 172800 > time()) {
            throw new RunTimeException("You can not attack this player in the first 48 hours");
        }
    }

    /**
     * @param BattleResult $battleResults
     * @param Fleet $fleet
     * @param array $attackerGameUnits
     * @param array $defenderGameUnits
     */
    private function processResults(BattleResult $battleResults, Fleet $fleet, array $attackerGameUnits, array $defenderGameUnits): void
    {
        $defendingPlayer = $fleet->getTargetWorldRegion()->getPlayer();

        if ($battleResults->hasWon()) {
            $this->updateBattleWon($fleet, $attackerGameUnits);
        } else {
            $this->updateBattleLost($fleet, $attackerGameUnits, $defenderGameUnits);

        }

        $this->networthUpdaterService->updateNetworthForPlayer($fleet->getPlayer());
        $this->networthUpdaterService->updateNetworthForPlayer($defendingPlayer);

        $this->incomeUpdaterService->updateIncomeForPlayer($fleet->getPlayer());
        $this->incomeUpdaterService->updateIncomeForPlayer($defendingPlayer);
    }

    /**
     * @param Fleet $fleet
     * @param array $attackerGameUnits
     */
    private function updateBattleWon(Fleet $fleet, array $attackerGameUnits): void
    {
        $targetWorldRegion = $fleet->getTargetWorldRegion();

        $targetWorldRegion->setPlayer($fleet->getPlayer());
        $this->worldRegionRepository->save($targetWorldRegion);

        foreach ($targetWorldRegion->getWorldRegionUnits() as $regionUnit) {
            $this->worldRegionUnitRepository->remove($regionUnit);
        }

        foreach ($attackerGameUnits as $fleetUnit) {
            $worldRegionUnit = WorldRegionUnit::create($targetWorldRegion, $fleetUnit->getGameUnit(), $fleetUnit->getAmount());
            $this->worldRegionUnitRepository->save($worldRegionUnit);
        }
        $this->fleetRepository->remove($fleet);
    }

    /**
     * XXX TODO: Improve code
     *
     * @param Fleet $fleet
     * @param array $attackerGameUnits
     * @param array $defenderGameUnits
     */
    private function updateBattleLost(Fleet $fleet, array $attackerGameUnits, array $defenderGameUnits): void
    {
        foreach ($fleet->getTargetWorldRegion()->getWorldRegionUnits() as $regionUnit) {
            $this->worldRegionUnitRepository->remove($regionUnit);
        }
        foreach ($defenderGameUnits as $worldRegionUnit) {
            $this->worldRegionUnitRepository->save($worldRegionUnit);
        }
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->fleetUnitRepository->remove($fleetUnit);
        }
        foreach ($attackerGameUnits as $fleetUnit) {
            $this->fleetUnitRepository->save($fleetUnit);
        }
    }
}
