<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\GameAccount;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseGameController extends BaseController
{
    /**
     * Get GameAccount
     *
     * @return GameAccount
     */
    public function getGameAccount(): GameAccount
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $em = $this->getDoctrine()->getManager();
        return $em->getRepository(GameAccount::class)
            ->findOneByMasterId($user->getId());
    }

    /**
     * Get Player
     *
     * @return Player
     */
    public function getPlayer(): Player
    {
        /**
         * XXX TODO: banned screen
         * XXX TODO: update screen
         * XXX TODO: Fix counter in missions/chat/messages navigation bar
         * XXX TODO: Fix session expired page
         */
        $gameAccount = $this->getGameAccount();
        $playerId = $this->get('session')->get('playerId');

        if(!$playerId) {
            throw new AccessDeniedException('Player is not set');
        }

        $em = $this->getDoctrine()->getManager();
        $player = $em->getRepository(Player::class)
            ->find($playerId);

        if (!$player) {
            throw new AccessDeniedException('Player is not set');
        }

        if ($player->getGameAccount()->getId() != $gameAccount->getId()) {
            throw new AccessDeniedException('Player does not belong to GameAccount');
        }

        if ($gameAccount->getActive() == 0) {
            throw new AccessDeniedException('GameAccount is not active!');
        }

        // XXX TODO: Should be run once on page request
        $gameEngine = new GameEngine($em);
        $gameEngine->run($player);

        return $player;
    }
}
