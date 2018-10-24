<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\Response;

final class HeadquarterController extends BaseGameController
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
    ) {
        $this->reportRepository = $reportRepository;
    }

    /**
     * XXX TODO: Fix me
     *
     * @return Response
     */
    public function army(): Response
    {
        return $this->render('game/headquarter/army.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @return Response
     */
    public function headquarter(): Response
    {
        $reports = $this->reportRepository->findReports($this->getPlayer(), 10);

        return $this->render('game/headquarter.html.twig', [
            'player' => $this->getPlayer(),
            'reports' => $reports
        ]);
    }

    /**
     * XXX TODO: Fix me
     *
     * @return Response
     */
    public function income(): Response
    {
        return $this->render('game/headquarter/income.html.twig', [
            'player' => $this->getPlayer(),
            'incomePop' => 0,
        ]);
    }

    /**
     * XXX TODO: Fix me
     *
     * @return Response
     */
    public function infrastructure(): Response
    {
        return $this->render('game/headquarter/infrastructure.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
