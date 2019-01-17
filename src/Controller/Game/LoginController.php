<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

final class LoginController extends BaseGameController
{
    /**
     * @return RedirectResponse
     */
    public function login(): RedirectResponse
    {
        try {
            $user = $this->getLoginUser();
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Site/Login');
        }

        $players = $user->getPlayers();

        if (count($players) == 0) {
            return $this->redirectToRoute('Game/Story/Chapter1', ['page' => 1]);
        } else {
            $player = $players->first();
            $this->get('session')->set('playerId', $player->getId());
            return $this->redirectToRoute('Game/Headquarter');
        }
    }

    /**
     * @param int $playerId
     * @param PlayerRepository $playerRepository
     * @return RedirectResponse
     */
    public function loginForPlayer(int $playerId, PlayerRepository $playerRepository): RedirectResponse
    {
        try {
            $user = $this->getLoginUser();
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Site/Login');
        }

        $player = $playerRepository->find($playerId);

        if (!$player) {
            return $this->redirectToRoute('Game/Login');
        }

        if ($player->getUser()->getId() != $user->getId()) {
            return $this->redirectToRoute('Game/Login');
        }

        $this->get('session')->set('playerId', $player->getId());
        return $this->redirectToRoute('Game/Headquarter');
    }

    /**
     * @return User
     */
    private function getLoginUser(): User
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new RuntimeException('You are not logged in!');
        }

        if ($user->isEnabled() !== true) {
            throw new RuntimeException('Your account is not enabled!');
        }

        if ($user->getActive() !== true) {
            throw new RuntimeException('You are banned!');
        }

        return $user;
    }
}
