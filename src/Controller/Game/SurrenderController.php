<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SurrenderController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function surrender(Request $request): Response
    {
        // XXX TODO: Remove market items
        return $this->render('game/surrender.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

}
