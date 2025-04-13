<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\Player;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
         */
        $user = $this->getGameUser();
        $playerId = $this->getPlayerIdFromSession();
        if ($playerId === null) {
            return $this->setSessionPlayerId($user);
        }

        foreach ($user->getPlayers() as $player) {
            if ($player->getId() === $playerId) {
                return $player;
            }
        }

        throw new AccessDeniedException('Player can not be found!');
    }

    private function getPlayerIdFromSession(): ?int
    {
        try {
            /** @var RequestStack $requestStack */
            $requestStack = $this->container->get('request_stack');
            /** @var int|null $playerId */
            $playerId = $requestStack->getSession()->get('playerId');
            return $playerId;
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            throw new AccessDeniedException('Player is not set');
        }
    }

    /**
     * When session expired user will be redirected to login page.
     * If login is successful, user is redirected to previous page,
     * which requires a valid playerId.
     *
     * Set playerId for first linked Player for smooth experience.
     */
    private function setSessionPlayerId(User $user): Player
    {
        $players = $user->getPlayers();

        if ($players->count() === 0 || ($players->first() instanceof Player) === false) {
            throw new AccessDeniedException('User has no Player!');
        }

        $player = $players->first();
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $requestStack->getSession()->set('playerId', $player->getId());

        return $player;
    }
}
