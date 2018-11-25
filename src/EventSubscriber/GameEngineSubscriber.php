<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GameEngineSubscriber extends BaseUserSubscriber implements EventSubscriberInterface
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
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * PlayerSubscriber constructor.
     *
     * @param GameEngine $gameEngine
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        GameEngine $gameEngine,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        PlayerRepository $playerRepository
    ) {
        parent::__construct($tokenStorage);
        $this->gameEngine = $gameEngine;
        $this->session = $session;
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

        if ($user->getActive() !== false) {
            $this->runGameEngine($user);
        }
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
