<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HeadquarterController extends BaseGameController
{
    /**
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @return Response
     */
    public function army(Request $request): Response
    {
        return $this->render('game/headquarter/army.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function headquarter(Request $request): Response
    {
        $em = $this->getEm();
        $reports = $em->getRepository('Game:Report')
            ->findReports($this->getPlayer(), 10);

        return $this->render('game/headquarter.html.twig', [
            'player' => $this->getPlayer(),
            'reports' => $reports
        ]);
    }

    /**
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @return Response
     */
    public function income(Request $request): Response
    {
        return $this->render('game/headquarter/income.html.twig', [
            'player' => $this->getPlayer(),
            'incomePop' => 0,
        ]);
    }

    /**
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @return Response
     */
    public function infrastructure(Request $request): Response
    {
        return $this->render('game/headquarter/infrastructure.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
