<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\PlayerState;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;

final class ResearchCompleter
{
    private ResearchPlayerRepository $researchPlayerRepository;
    private ReportRepository $reportRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private ResearchRegistry $researchRegistry;

    public function __construct(
        ResearchPlayerRepository $researchPlayerRepository,
        ReportRepository $reportRepository,
        NetWorthUpdaterService $netWorthUpdaterService,
        ResearchRegistry $researchRegistry
    ) {
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->reportRepository = $reportRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->researchRegistry = $researchRegistry;
    }

    /**
     * Activates a completed research. Income and upkeep are not touched here: the PlayerStateUpdaterService
     * accrues resources up to the completion time first and recalculates the rate afterwards.
     */
    public function complete(ResearchPlayer $researchPlayer): void
    {
        $researchPlayer->setActive(true);

        $player = $researchPlayer->getPlayer();

        $research = $this->researchRegistry->find($researchPlayer->getResearchSlug());
        $researchName = $research !== null ? $research->getName() : $researchPlayer->getResearchSlug();
        $finishedTimestamp = $researchPlayer->getCompletionTimestamp();
        $level = $researchPlayer->getLevel();
        $message = $research !== null && $research->getMaxLevel() > 1
            ? "You successfully researched {$researchName} level {$level}"
            : "You successfully researched a new technology: {$researchName}";
        $report = Report::createForPlayer($player, $finishedTimestamp, 2, $message);

        $this->reportRepository->save($report);
        $this->researchPlayerRepository->save($researchPlayer);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
    }
}
