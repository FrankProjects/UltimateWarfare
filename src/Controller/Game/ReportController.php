<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\Response;

final class ReportController extends BaseGameController
{
    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * ReportController constructor.
     *
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        ReportRepository $reportRepository
    )
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param int $type
     * @return Response
     */
    public function report(int $type): Response
    {
        switch ($type):
            case Report::TYPE_ATTACKED:
                $reportQueryVariable = "attacked";
                $reportSubject = "Battle reports";
                $reportSummary = "You will see your battle reports here.";
                break;

            case Report::TYPE_GENERAL:
                $reportQueryVariable = "general";
                $reportSubject = "General reports";
                $reportSummary = "You will see your general reports here.";
                break;

            case Report::TYPE_MARKET:
                $reportQueryVariable = "market";
                $reportSubject = "Market reports";
                $reportSummary = "You will see your market reports here.";
                break;

            case Report::TYPE_AID:
                $reportQueryVariable = "aid";
                $reportSubject = "Aid reports";
                $reportSummary = "You will see your aid reports here.";
                break;

            default:
                $reportQueryVariable = "all";
                $reportSubject = "All reports";
                $reportSummary = "You will see your reports here.";
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

        if ($reportQueryVariable == 'all') {
            $reports = $this->reportRepository->findReports($this->getPlayer());
        } else {
            $reports = $this->reportRepository->findReportsByType($this->getPlayer(), $type);
        }

        return $this->render('game/reports.html.twig', [
            'player' => $this->getPlayer(),
            'reports' => $reports,
            'reportSummary' => $reportSummary,
            'reportSubject' => $reportSubject
        ]);
    }
}
