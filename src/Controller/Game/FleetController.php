<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FleetController extends BaseGameController
{
    /**
     * @var FleetRepository
     */
    private $fleetRepository;

    /**
     * FleetController constructor.
     * @param FleetRepository $fleetRepository
     */
    public function __construct(FleetRepository $fleetRepository)
    {
        $this->fleetRepository = $fleetRepository;
    }

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
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            $this->addFlash('error', 'No such fleet!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $this->addFlash('error', 'This is not your fleet');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getWorldRegion()->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $this->addFlash('error', 'You are not owner if this region!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        // XXX TODO: fix recall fleet units
        $this->fleetRepository->remove($fleet);

        $this->addFlash('success', 'You succesfully recalled your forces!');

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
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null) {
            $this->addFlash('error', 'No such fleet!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $this->addFlash('error', 'This is not your fleet');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getTargetWorldRegion()->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $this->addFlash('error', 'You are not owner if this region!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        // XXX TODO: fix reinforce fleet units
        $this->fleetRepository->remove($fleet);

        $this->addFlash('success', 'You succesfully reinforced your region!');

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
