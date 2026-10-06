<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;

final class ResearchProcessor implements Processor
{
    private ResearchPlayerRepository $researchPlayerRepository;
    private ReportRepository $reportRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private ResearchRegistry $researchRegistry;
    private int $researchTimeOverride;

    public function __construct(
        ResearchPlayerRepository $researchPlayerRepository,
        ReportRepository $reportRepository,
        NetWorthUpdaterService $netWorthUpdaterService,
        ResearchRegistry $researchRegistry,
        int $researchTimeOverride
    ) {
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->reportRepository = $reportRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->researchRegistry = $researchRegistry;
        $this->researchTimeOverride = $researchTimeOverride;
    }

    public function run(int $timestamp): void
    {
        // Override research time for testing purposes
        if ($this->researchTimeOverride > 0) {
            $researches = $this->researchPlayerRepository->getAllNonActiveResearch();
        } else {
            $researches = $this->researchPlayerRepository->getNonActiveCompletedResearch($timestamp);
        }

        foreach ($researches as $researchPlayer) {
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
}
