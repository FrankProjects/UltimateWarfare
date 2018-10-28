<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProfileController extends BaseGameController
{
    /**
     * @param Request $request
     * @param string $playerName
     * @return Response
     * @throws \Exception
     */
    public function profile(Request $request, string $playerName): Response
    {
        $em = $this->getEm();
        $profilePlayer = $em->getRepository('Game:Player')
            -> findOneBy(['name' => $playerName]);

        if (!$profilePlayer) {
            return $this->render('game/playerNotFound.html.twig');
        }

        return $this->render('game/profile.html.twig', [
            'player' => $this->getPlayer(),
            'profilePlayer' => $profilePlayer,
        ]);
    }
}
