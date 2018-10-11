<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseGameController extends BaseController
{
    /**
     * Get User
     *
     * @return User
     */
    public function getGameUser(): User
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($user->isEnabled() !== true) {
            throw new AccessDeniedException('User is not enabled!');
        }

        if ($user->getActive() !== true) {
            throw new AccessDeniedException('User is not active!');
        }

        return $user;
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
        $user = $this->getGameUser();
        $playerId = $this->get('session')->get('playerId');

        if (!$playerId) {
            throw new AccessDeniedException('Player is not set');
        }

        $em = $this->getDoctrine()->getManager();
        $player = $em->getRepository(Player::class)
            ->find($playerId);

        if (!$player) {
            throw new AccessDeniedException('Player is not set');
        }

        if ($player->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedException('Player does not belong to User');
        }

        // XXX TODO: Should be run once on page request
        $this->updateGameState($player);

        return $player;
    }

    /**
     * @param Player $player
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function updateGameState(Player $player)
    {
        $gameEngine = $this->container->get(GameEngine::class);
        $gameEngine->run($player);
    }
}
