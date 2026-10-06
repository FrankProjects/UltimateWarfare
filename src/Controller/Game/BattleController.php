<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

final class BattleController extends BaseGameController
{
    private BattleEngine $battleEngine;
    private FleetRepository $fleetRepository;

    public function __construct(
        BattleEngine $battleEngine,
        FleetRepository $fleetRepository
    ) {
        $this->battleEngine = $battleEngine;
        $this->fleetRepository = $fleetRepository;
    }

    public function battleApi(int $fleetId): JsonResponse
    {
        $player = $this->getPlayer();
        $fleet = $this->fleetRepository->findByIdAndPlayer($fleetId, $player);

        if ($fleet === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Fleet does not exist'
            ], 404);
        }

        try {
            $battleResults = $this->battleEngine->battle($fleet);

            // Collect battle log from all phases
            $battleLog = [];
            foreach ($battleResults->getBattlePhases() as $battlePhase) {
                foreach ($battlePhase->getBattleLog() as $line) {
                    $battleLog[] = $line;
                }
            }

            return new JsonResponse([
                'success' => true,
                'hasWon' => $battleResults->hasWon(),
                'battleLog' => $battleLog,
                'message' => $battleResults->hasWon() ? 'Victory! You won the battle!' : 'Defeat! You lost the battle.'
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
