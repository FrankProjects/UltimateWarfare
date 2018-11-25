<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PlayerSubscriber constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event): void
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
        /** @var TokenInterface $token */
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token === null) {
            return null;
        }

        /** @var User $user */
        $user = $token->getUser();
        return $user;
    }

    /**
     * @param FilterResponseEvent $event
     */
    private function checkBannedAndRedirect(FilterResponseEvent $event): void
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
            $this->container->get('router')->generate('Game/Banned', [], UrlGeneratorInterface::ABSOLUTE_PATH)
        );
        $event->setResponse($response);
    }

    /**
     * @param User $user
     */
    private function runGameEngine(User $user): void
    {
        $playerId = $this->container->get('session')->get('playerId');

        if (!$playerId) {
            return;
        }

        foreach ($user->getPlayers() as $player) {
            if ($player->getId() === $playerId) {
                $gameEngine = $this->container->get(GameEngine::class);
                $gameEngine->run($player);
            }
        }
    }
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }
}
