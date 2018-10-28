<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HallOfFameController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function hallOfFame(Request $request): Response
    {
        $em = $this->getEm();
        $history = $em->getRepository('Game:History')
            ->findAll();

        return $this->render('site/hallOfFame.html.twig', [
            'history' => $history
        ]);
    }

    /**
     * XXX TODO: order by
     * @param Request $request
     * @param int $worldId
     * @param int $round
     * @return Response
     */
    public function round(Request $request, int $worldId, int $round): Response
    {
        $em = $this->getEm();
        $federations = $em->getRepository('Game:HistoryFederation')
            ->findBy(['worldId' => $worldId, 'round' => $round], ['regions' => 'DESC']);

        $players = $em->getRepository('Game:HistoryPlayer')
            ->findBy(['worldId' => $worldId, 'round' => $round], ['regions' => 'DESC']);

        return $this->render('site/hallOfFameRound.html.twig', [
            'federations' => $federations,
            'players' => $players
        ]);
    }
}
