<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Service\BattleEngine;
use Symfony\Component\HttpFoundation\Response;
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

    public function battle(int $fleetId): Response
    {
        $player = $this->getPlayer();
        $fleet = $this->fleetRepository->findByIdAndPlayer($fleetId, $player);
        if ($fleet === null) {
            $this->addFlash('error', 'Fleet does not exist');
            return $this->redirectToRoute('Game/Fleets', [], 302);
        }

        try {
            $battleResults = $this->battleEngine->battle($fleet);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/Fleets', [], 302);
        }

        return $this->render('game/battle.html.twig', [
            'player' => $player,
            'battleResults' => $battleResults,
            'hasWon' => $battleResults->hasWon()
        ]);
    }
}
