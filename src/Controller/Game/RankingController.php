<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RankingController extends BaseGameController
{
    /**
     * @param Request $request
     * @param string $sortBy
     * @return Response
     */
    public function ranking(Request $request, string $sortBy): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();

        if ($sortBy == 'region') {
            $rankingsTitle = "Rankings by Regions (Top 10)";
            $players = $em->getRepository('Game:Player')
                ->findByWorldAndRegions($player->getWorld());
        }else{
            $rankingsTitle = "Rankings by Networth (Top 10)";
            $players = $em->getRepository('Game:Player')
                ->findByWorldAndNetworth($player->getWorld());
        }

        return $this->render('game/rankings.html.twig', [
            'player' => $player,
            'players' => $players,
            'rankingsTitle' => $rankingsTitle
        ]);
    }
}
