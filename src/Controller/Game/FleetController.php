<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Service\FleetActionService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FleetController extends BaseGameController
{
    /**
     * @return Response
     */
    public function fleetList(): Response
    {
        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param int $fleetId
     * @param FleetActionService $fleetActionService
     * @return Response
     */
    public function recall(int $fleetId, FleetActionService $fleetActionService): Response
    {
        try {
            $fleetActionService->recall($fleetId, $this->getPlayer());
            $this->addFlash('success', 'You successfully recalled your troops!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param int $fleetId
     * @param FleetActionService $fleetActionService
     * @return Response
     */
    public function reinforce(int $fleetId, FleetActionService $fleetActionService): Response
    {
        try {
            $fleetActionService->reinforce($fleetId, $this->getPlayer());
            $this->addFlash('success', 'You successfully reinforced your region!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
