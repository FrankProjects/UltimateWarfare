<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends BaseGameController
{
    /**
     * @return Response
     */
    public function login(): Response
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
     * @param int $playerId
     * @param PlayerRepository $playerRepository
     * @return Response
     */
    public function loginForPlayer(int $playerId, PlayerRepository $playerRepository): Response
    {
        $user = $this->getGameUser();
        $player = $playerRepository->find($playerId);

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
