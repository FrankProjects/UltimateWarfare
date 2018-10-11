<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Service\Fleet as FleetService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FleetController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function fleetList(Request $request): Response
    {
        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param Request $request
     * @param int $fleetId
     * @return Response
     */
    public function recall(Request $request, int $fleetId): Response
    {
        $fleetService = $this->container->get(FleetService::class);
        try {
            $fleetService->recall($fleetId, $this->getPlayer());
            $this->addFlash('success', 'You successfully recalled your troops!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param Request $request
     * @param int $fleetId
     * @return Response
     */
    public function reinforce(Request $request, int $fleetId): Response
    {
        $fleetService = $this->container->get(FleetService::class);
        try {
            $fleetService->reinforce($fleetId, $this->getPlayer());
            $this->addFlash('success', 'You successfully reinforced your region!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
