<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;

final class ReportCreator
{
    private ReportRepository $reportRepository;

    public function __construct(
        ReportRepository $reportRepository
    ) {
        $this->reportRepository = $reportRepository;
    }

    public function createReport(Player $player, int $timestamp, string $report, int $type = Report::TYPE_ATTACKED): void
    {
        $report = Report::createForPlayer($player, $timestamp, $type, $report);
        $this->reportRepository->save($report);
    }
}
