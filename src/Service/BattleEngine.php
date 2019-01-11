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
     * XXX TODO: Add bunker/defense objects
     * XXX TODO: Add train station/airfield/harbor support
     * XXX TODO: Add minesweeper support
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

        $battlePhases = [
            BattlePhase::AIR_PHASE,
            BattlePhase::SEA_PHASE,
            BattlePhase::GROUND_PHASE
        ];

        $battlePhaseResults = [];
        foreach ($battlePhases as $battlePhaseName) {
            $battlePhase = BattlePhase::factory($battlePhaseName, $attackerGameUnits, $defenderGameUnits);
            $battlePhase->startBattlePhase();

            $attackerGameUnits = $battlePhase->getAttackerGameUnits();
            $defenderGameUnits = $battlePhase->getDefenderGameUnits();

            $battlePhaseResults[] = $battlePhase;
        }

        $battleResults = new BattleResult($battlePhaseResults);

        $attackingPlayer = $fleet->getPlayer();
        $defendingPlayer = $fleet->getTargetWorldRegion()->getPlayer();

        $targetWorldRegion = $fleet->getTargetWorldRegion();

        if ($battleResults->hasWon()) {
            $targetWorldRegion->setPlayer($attackingPlayer);
            $this->worldRegionRepository->save($targetWorldRegion);

            foreach ($targetWorldRegion->getWorldRegionUnits() as $regionUnit) {
                $this->worldRegionUnitRepository->remove($regionUnit);
            }

            foreach ($attackerGameUnits as $fleetUnit) {
                $worldRegionUnit = WorldRegionUnit::create($targetWorldRegion, $fleetUnit->getGameUnit(), $fleetUnit->getAmount());
                $this->worldRegionUnitRepository->save($worldRegionUnit);
            }
            $this->fleetRepository->remove($fleet);
        } else {
            // XXX TODO: Improve code
            foreach ($targetWorldRegion->getWorldRegionUnits() as $regionUnit) {
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

        $this->networthUpdaterService->updateNetworthForPlayer($attackingPlayer);
        $this->networthUpdaterService->updateNetworthForPlayer($defendingPlayer);

        $this->incomeUpdaterService->updateIncomeForPlayer($attackingPlayer);
        $this->incomeUpdaterService->updateIncomeForPlayer($defendingPlayer);

        return $battleResults;
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
}
