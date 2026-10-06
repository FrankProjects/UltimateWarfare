<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ReportController extends BaseGameController
{
    private const int REPORTS_PER_PAGE = 25;

    private ReportRepository $reportRepository;

    public function __construct(
        ReportRepository $reportRepository
    ) {
        $this->reportRepository = $reportRepository;
    }

    public function reportsApi(Request $request): JsonResponse
    {
        $player = $this->getPlayer();
        $page = max(1, $request->query->getInt('page', 1));
        $typeParam = $request->query->get('type');

        $type = null;
        if ($typeParam !== null && $typeParam !== '' && $typeParam !== 'all') {
            $type = (int) $typeParam;
            $validTypes = [Report::TYPE_ATTACKED, Report::TYPE_GENERAL, Report::TYPE_MARKET, Report::TYPE_AID];
            if (!in_array($type, $validTypes, true)) {
                $type = null;
            }
        }

        $offset = ($page - 1) * self::REPORTS_PER_PAGE;
        $reports = $this->reportRepository->findReportsPaginated($player, $type, self::REPORTS_PER_PAGE, $offset);
        $totalReports = $this->reportRepository->countReports($player, $type);
        $totalPages = (int) ceil($totalReports / self::REPORTS_PER_PAGE);

        $reportsData = [];
        foreach ($reports as $report) {
            $reportsData[] = [
                'id' => $report->getId(),
                'type' => $report->getType(),
                'typeName' => Report::getReportSubject($report->getType()),
                'timestamp' => $report->getTimestamp(),
                'date' => date('M d, Y H:i', $report->getTimestamp()),
                'report' => $report->getReport(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'reports' => $reportsData,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalReports' => $totalReports,
                'perPage' => self::REPORTS_PER_PAGE,
            ],
            'categories' => [
                ['id' => 'all', 'name' => 'All Reports'],
                ['id' => Report::TYPE_ATTACKED, 'name' => 'Battle Reports'],
                ['id' => Report::TYPE_GENERAL, 'name' => 'General Reports'],
                ['id' => Report::TYPE_MARKET, 'name' => 'Market Reports'],
                ['id' => Report::TYPE_AID, 'name' => 'Aid Reports'],
            ],
        ]);
    }
}
