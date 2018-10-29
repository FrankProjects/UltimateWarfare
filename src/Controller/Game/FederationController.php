<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FederationController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function federation(Request $request): Response
    {
        $player = $this->getPlayer();
        if ($player->getFederation() == null) {
            return $this->render('game/federation/noFederation.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }
        return $this->render('game/federation/federation.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->render('game/federation/create.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function join(Request $request): Response
    {
        return $this->render('game/federation/join.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
