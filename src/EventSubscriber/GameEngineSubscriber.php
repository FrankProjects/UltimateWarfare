<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GameEngineSubscriber extends AbstractUserSubscriber implements EventSubscriberInterface
{
    private GameEngine $gameEngine;
    private RequestStack $requestStack;
    private PlayerRepository $playerRepository;

    public function __construct(
        GameEngine $gameEngine,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        PlayerRepository $playerRepository
    ) {
        parent::__construct($tokenStorage);
        $this->gameEngine = $gameEngine;
        $this->requestStack = $requestStack;
        $this->playerRepository = $playerRepository;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null) {
            return;
        }

        if ($user->getActive() !== false) {
            $this->runGameEngine($user);
        }
    }

    private function runGameEngine(User $user): void
    {
        /** @var int|null $playerId */
        $playerId = $this->requestStack->getSession()->get('playerId');

        if ($playerId === null) {
            return;
        }

        $player = $this->playerRepository->find($playerId);
        if ($player === null) {
            return;
        }

        if ($player->getUser()->getId() !== $user->getId()) {
            return;
        }

        try {
            $this->gameEngine->run($player);
        } catch (\Exception $exception) {
            // Do nothing...
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
