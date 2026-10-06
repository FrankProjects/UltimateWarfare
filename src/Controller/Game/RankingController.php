<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

final class RankingController extends BaseGameController
{
    public function rankingApi(PlayerRepository $playerRepository): JsonResponse
    {
        $player = $this->getPlayer();
        $players = $playerRepository->findByWorldAndRegions($player->getWorld());

        $rankings = [];
        foreach ($players as $rankedPlayer) {
            $rankings[] = [
                'name' => $rankedPlayer->getName(),
                'federation' => $rankedPlayer->getFederation()?->getName(),
                'regions' => count($rankedPlayer->getWorldRegions()),
                'netWorth' => $rankedPlayer->getNetWorth(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'rankings' => $rankings,
        ]);
    }
}
