<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    /**
     * @var GameEngine
     */
    private $gameEngine;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * PlayerSubscriber constructor.
     *
     * @param GameEngine $gameEngine
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        GameEngine $gameEngine,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        PlayerRepository $playerRepository
    ) {
        $this->gameEngine = $gameEngine;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null) {
            return;
        }

        if ($user->getActive() === false) {
            $this->checkBannedAndRedirect($event);
        } else {
            $this->runGameEngine($user);
        }
    }

    /**
     * @return User|null
     */
    private function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        /** @var User $user */
        $user = $token->getUser();
        return $user;
    }

    /**
     * @param GetResponseEvent $event
     */
    private function checkBannedAndRedirect(GetResponseEvent $event): void
    {
        if (
            strpos($event->getRequest()->getRequestUri(), '/game') === false &&
            strpos($event->getRequest()->getRequestUri(), '/forum') === false
        ) {
            return;
        }

        if (strpos($event->getRequest()->getRequestUri(), '/game/banned') !== false) {
            return;
        }

        $response = new RedirectResponse(
            $this->router->generate('Game/Banned', [], UrlGeneratorInterface::ABSOLUTE_PATH)
        );
        $event->setResponse($response);
    }

    /**
     * @param User $user
     */
    private function runGameEngine(User $user): void
    {
        $playerId = $this->session->get('playerId');

        if (!$playerId) {
            return;
        }

        $player = $this->playerRepository->find($playerId);
        if ($player->getUser()->getId() !== $user->getId()) {
            return;
        }

        try {
            $this->gameEngine->run($player);
        } catch (\Exception $exception) {
            // Do nothing...
        }
    }
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
