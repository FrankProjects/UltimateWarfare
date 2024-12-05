<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Response;

final class RankingController extends BaseGameController
{
    public function ranking(string $sortBy, PlayerRepository $playerRepository): Response
    {
        $player = $this->getPlayer();

        if ($sortBy === 'region') {
            $rankingsTitle = "Rankings by Regions (Top 10)";
            $players = $playerRepository->findByWorldAndRegions($player->getWorld());
        } else {
            $rankingsTitle = "Rankings by Networth (Top 10)";
            $players = $playerRepository->findByWorldAndNetworth($player->getWorld());
        }

        return $this->render(
            'game/rankings.html.twig',
            [
                'player' => $player,
                'players' => $players,
                'rankingsTitle' => $rankingsTitle
            ]
        );
    }
}
