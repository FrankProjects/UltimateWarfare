<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Response;

final class ProfileController extends BaseGameController
{
    public function profile(string $playerName, PlayerRepository $playerRepository): Response
    {
        $player = $this->getPlayer();
        $profilePlayer = $playerRepository->findByNameAndWorld($playerName, $player->getWorld());

        if (!$profilePlayer) {
            $this->addFlash('error', 'Player profile can not be found!');
            return $this->redirectToRoute('Game/Headquarter');
        }

        return $this->render('game/profile.html.twig', [
            'player' => $player,
            'profilePlayer' => $profilePlayer,
        ]);
    }
}
