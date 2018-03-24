<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\GameAccount;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $gameAccount = $this->getGameAccount();
        $em = $this->getEm();

        if (!$gameAccount) {
            // Setup game account
            // Get default MapDesign
            // XXX TODO: make setting?
            $mapDesign = $em->getRepository('Game:MapDesign')
                ->find(3);

            $gameAccount = GameAccount::create($this->getUser()->getId(), $request->getClientIp(), $mapDesign);
            $em->persist($gameAccount);
            $em->flush();

            return $this->redirectToRoute('Story/Chapter1', array('page' => 1), 302);
        } else {

            $player = $em->getRepository('Game:Player')
                ->findOneByGameAccount($gameAccount);

            if (count($player) == 0) {
                return $this->redirectToRoute('SelectWorld', array(), 302);
            } else {
                $this->get('session')->set('playerId', $player->getId());
                return $this->redirectToRoute('Game/Headquarter', array(), 302);
            }
        }
    }

    /**
     * @param Request $request
     * @param int $playerId
     * @return Response
     */
    public function loginForPlayer(Request $request, int $playerId): Response
    {
        $gameAccount = $this->getGameAccount();
        $em = $this->getEm();

        $player = $em->getRepository('Game:Player')
            ->find($playerId);

        if (!$player) {
            return $this->redirectToRoute('Game/Login', array(), 302);
        }

        if ($player->getGameAccount()->getId() != $gameAccount->getId()) {
            return $this->redirectToRoute('Game/Login', array(), 302);
        }

        $this->get('session')->set('playerId', $player->getId());
        return $this->redirectToRoute('Game/Headquarter', array(), 302);
    }
}
