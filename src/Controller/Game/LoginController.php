<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $user = $this->getGameUser();
        $players = $user->getPlayers();

        if (count($players) == 0) {
            return $this->redirectToRoute('Game/Story/Chapter1', ['page' => 1]);
        } else {
            $player = $players->first();
            $this->get('session')->set('playerId', $player->getId());
            return $this->redirectToRoute('Game/Headquarter');
        }
    }

    /**
     * @param Request $request
     * @param int $playerId
     * @return Response
     */
    public function loginForPlayer(Request $request, int $playerId): Response
    {
        $user = $this->getGameUser();
        $em = $this->getEm();

        $player = $em->getRepository('Game:Player')
            ->find($playerId);

        if (!$player) {
            return $this->redirectToRoute('Game/Login');
        }

        if ($player->getUser()->getId() != $user->getId()) {
            return $this->redirectToRoute('Game/Login');
        }

        $this->get('session')->set('playerId', $player->getId());
        return $this->redirectToRoute('Game/Headquarter');
    }
}
