<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ProfileController extends BaseGameController
{
    public function playerProfileApi(string $playerName, PlayerRepository $playerRepository): JsonResponse
    {
        $player = $this->getPlayer();
        $profilePlayer = $playerRepository->findByNameAndWorld($playerName, $player->getWorld());

        if ($profilePlayer === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Player not found',
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'profile' => [
                'name' => $profilePlayer->getName(),
                'joinDate' => date('Y-m-d H:i:s', $profilePlayer->getTimestampJoined()),
                'regions' => count($profilePlayer->getWorldRegions()),
                'netWorth' => $profilePlayer->getNetWorth(),
                'federation' => $profilePlayer->getFederation()?->getName(),
            ],
        ]);
    }
}
