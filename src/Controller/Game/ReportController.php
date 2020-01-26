<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\Response;

final class ReportController extends BaseGameController
{
    private ReportRepository $reportRepository;

    public function __construct(
        ReportRepository $reportRepository
    ) {
        $this->reportRepository = $reportRepository;
    }

    public function report(int $type): Response
    {
        switch ($type):
            case Report::TYPE_ATTACKED:
            case Report::TYPE_GENERAL:
            case Report::TYPE_MARKET:
            case Report::TYPE_AID:
                $reports = $this->reportRepository->findReportsByType($this->getPlayer(), $type);
        break;
        default:
                $reports = $this->reportRepository->findReports($this->getPlayer());
        endswitch;

        /**
         * XXX TODO: fix me
         *     if ($report_query_variable =="all") {
         * $query = "update player set general = 0, aid = 0, attacked = 0, market = 0 where id = $player_id";
         * }else{
         * $query = "update player set $report_query_variable = 0 where id = $player_id";
         * }
         *
         */

        return $this->render('game/reports.html.twig', [
            'player' => $this->getPlayer(),
            'reports' => $reports,
            'reportSubject' => Report::getReportSubject($type)
        ]);
    }
}
