<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReportController extends BaseGameController
{
    /**
     * @param Request $request
     * @param int $type
     * @return Response
     */
    public function report(Request $request, int $type): Response
    {
        switch ($type):
            case 1:
                $reportQueryVariable = "attacked";
                $reportSubject = "Battle reports";
                $reportSummary = "You will see your battle reports here.";
                $reportType = 1;
                break;

            case 2:
                $reportQueryVariable = "general";
                $reportSubject = "General reports";
                $reportSummary = "You will see your general reports here.";
                $reportType = 2;
                break;

            case 4:
                $reportQueryVariable = "market";
                $reportSubject = "Market reports";
                $reportSummary = "You will see your market reports here.";
                $reportType = 4;
                break;

            case 5:
                $reportQueryVariable = "aid";
                $reportSubject = "Aid reports";
                $reportSummary = "You will see your aid reports here.";
                $reportType = 5;
                break;

            default:
                $reportQueryVariable = "all";
                $reportSubject = "All reports";
                $reportSummary = "You will see your reports here.";
                $reportType = 10;
        endswitch;

        /**
         * XXX TODO: fix me
         *     if ($report_query_variable =="all") {
        $query = "update player set general = 0, aid = 0, attacked = 0, market = 0 where id = $player_id";
        }else{
        $query = "update player set $report_query_variable = 0 where id = $player_id";
        }
         *
         */

        $em = $this->getEm();

        if ($reportQueryVariable == 'all') {
            $reports = $em->getRepository('Game:Report')
                ->findReports($this->getPlayer());
        } else {
            $reports = $em->getRepository('Game:Report')
                ->findReportsByType($this->getPlayer(), $reportType);
        }

        return $this->render('game/reports.html.twig', [
            'player' => $this->getPlayer(),
            'reports' => $reports,
            'reportSummary' => $reportSummary,
            'reportSubject' => $reportSubject
        ]);
    }
}
