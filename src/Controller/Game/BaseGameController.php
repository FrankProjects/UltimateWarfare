<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\Player;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseGameController extends BaseController
{
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

    public function getPlayer(): Player
    {
        /**
         * XXX TODO: Fix counter in messages navigation bar
         * XXX TODO: Fix session expired page
         */
        $user = $this->getGameUser();
        $playerId = $this->getPlayerIdFromSession();

        foreach ($user->getPlayers() as $player) {
            if ($player->getId() === $playerId) {
                return $player;
            }
        }

        throw new AccessDeniedException('Player can not be found!');
    }

    private function getPlayerIdFromSession(): int
    {
        try {
            return $this->container->get('request_stack')->getSession()->get('playerId');
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            throw new AccessDeniedException('Player is not set');
        }
    }
}
