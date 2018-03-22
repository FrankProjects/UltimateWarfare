<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $em = $this->getEm();
        $fleet = $em->getRepository('Game:Fleet')
            ->find($fleetId);

        if ($fleet === null) {
            $request->getSession()->getFlashBag()->add('error', 'No such fleet!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $request->getSession()->getFlashBag()->add('error', 'This is not your fleet');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getWorldRegion()->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $request->getSession()->getFlashBag()->add('error', 'You are not owner if this region!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $em->remove($fleetUnit);
            // XXX TODO: fix recall fleet units
        }

        $em->remove($fleet);
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', 'You succesfully recalled your forces!');

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
        $em = $this->getEm();
        $fleet = $em->getRepository('Game:Fleet')
            ->find($fleetId);

        if ($fleet === null) {
            $request->getSession()->getFlashBag()->add('error', 'No such fleet!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $request->getSession()->getFlashBag()->add('error', 'This is not your fleet');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        if ($fleet->getTargetWorldRegion()->getPlayer()->getId() != $this->getPlayer()->getId()) {
            $request->getSession()->getFlashBag()->add('error', 'You are not owner if this region!');

            return $this->render('game/fleetList.html.twig', [
                'player' => $this->getPlayer()
            ]);
        }

        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $em->remove($fleetUnit);
            // XXX TODO: fix reinforce fleet units
        }

        $em->remove($fleet);
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', 'You succesfully reinforced your region!');

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }
}
