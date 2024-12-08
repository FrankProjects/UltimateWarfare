<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;

final class ResearchProcessor implements Processor
{
    private ResearchPlayerRepository $researchPlayerRepository;
    private ReportRepository $reportRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;

    public function __construct(
        ResearchPlayerRepository $researchPlayerRepository,
        ReportRepository $reportRepository,
        NetWorthUpdaterService $netWorthUpdaterService
    ) {
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->reportRepository = $reportRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
    }

    public function run(int $timestamp): void
    {
        $researches = $this->researchPlayerRepository->getNonActiveCompletedResearch($timestamp);

        foreach ($researches as $researchPlayer) {
            $researchPlayer->setActive(true);

            $player = $researchPlayer->getPlayer();

            $research = $researchPlayer->getResearch();
            $finishedTimestamp = $researchPlayer->getTimestamp() + $research->getTimestamp();
            $message = "You successfully researched a new technology: {$research->getName()}";
            $report = Report::createForPlayer($player, $finishedTimestamp, 2, $message);

            $this->reportRepository->save($report);
            $this->researchPlayerRepository->save($researchPlayer);

            $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
        }
    }
}
