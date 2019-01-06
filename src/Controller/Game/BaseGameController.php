<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\Player;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseGameController extends BaseController
{
    /**
     * Get User
     *
     * @param bool $checkActive
     * @return User
     */
    public function getGameUser(bool $checkActive = true): User
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($user->isEnabled() !== true) {
            throw new AccessDeniedException('User is not enabled!');
        }

        if ($checkActive && $user->getActive() !== true) {
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
         * XXX TODO: Fix counter in chat/messages navigation bar
         * XXX TODO: Fix session expired page
         */
        $user = $this->getGameUser();
        $playerId = $this->get('session')->get('playerId');

        if (!$playerId) {
            throw new AccessDeniedException('Player is not set');
        }

        foreach ($user->getPlayers() as $player) {
            if ($player->getId() === $playerId) {
                return $player;
            }
        }

        throw new AccessDeniedException('Player can not be found!');
    }
}
