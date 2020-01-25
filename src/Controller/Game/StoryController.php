<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class StoryController extends BaseGameController
{
    public function page(int $page): Response
    {
        switch ($page) {
            case 1:
                return $this->render('game/story/chapter1_1.html.twig');
            case 2:
                $user = $this->getGameUser();
                $players = $user->getPlayers();

                return $this->render('game/story/chapter1_2.html.twig', [
                    'players' => $players
                ]);
            default:
                throw new NotFoundHttpException("Page not found!");
        }
    }
}
