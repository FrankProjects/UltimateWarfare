<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Response;

final class ProfileController extends BaseGameController
{
    /**
     * @param string $playerName
     * @param PlayerRepository $playerRepository
     * @return Response
     */
    public function profile(string $playerName, PlayerRepository $playerRepository): Response
    {
        $player = $this->getPlayer();
        $profilePlayer = $playerRepository->findByNameAndWorld($playerName, $player->getWorld());

        if (!$profilePlayer) {
            return $this->render('game/playerNotFound.html.twig');
        }

        return $this->render('game/profile.html.twig', [
            'player' => $player,
            'profilePlayer' => $profilePlayer,
        ]);
    }
}
