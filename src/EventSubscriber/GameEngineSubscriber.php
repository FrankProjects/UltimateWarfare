<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Runs the game engine for every request of a player.
 *
 * Requests that change state (POST, DELETE, ...) run in one database transaction that holds a row lock on the
 * current player from the start of the request until the response: concurrent requests of the same player
 * can't read the same resources and overwrite each other's changes. The transaction commits for successful
 * responses (status < 400) and rolls back for error responses and exceptions, so a failed action never leaves
 * half of its changes behind. Actions that also change another player or shared row lock those explicitly
 * (GameEngine::syncPlayer(), EntityLockRepository::lockForUpdate()).
 *
 * Safe requests (GET) only read, so they don't take the lock and run in parallel.
 */
final class GameEngineSubscriber extends AbstractUserSubscriber implements EventSubscriberInterface
{
    private GameEngine $gameEngine;
    private RequestStack $requestStack;
    private PlayerRepository $playerRepository;
    private EntityManagerInterface $entityManager;
    private bool $transactionStarted = false;

    public function __construct(
        GameEngine $gameEngine,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($tokenStorage);
        $this->gameEngine = $gameEngine;
        $this->requestStack = $requestStack;
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null || $user->isBanned()) {
            return;
        }

        $player = $this->getSessionPlayer($user);
        if ($player === null) {
            return;
        }

        if ($event->getRequest()->isMethodSafe()) {
            $this->runGameEngine($player);
            return;
        }

        // Complete other players' due queue items first; each is synced in its own transaction
        $this->runGameEngine(null);

        $this->entityManager->beginTransaction();
        $this->transactionStarted = true;

        // Nested in the request transaction: the row lock on the player is held until the response
        $this->gameEngine->syncPlayer($player, time());
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->transactionStarted) {
            return;
        }
        $this->transactionStarted = false;

        if ($event->getResponse()->getStatusCode() >= 400) {
            $this->rollback();
            return;
        }

        try {
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            // Turned into an error response by the kernel, so the client never sees a success that wasn't saved
            $this->rollback();
            throw $e;
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->transactionStarted) {
            return;
        }
        $this->transactionStarted = false;

        $this->rollback();
    }

    private function rollback(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
    }

    private function getSessionPlayer(User $user): ?Player
    {
        /** @var int|null $playerId */
        $playerId = $this->requestStack->getSession()->get('playerId');

        if ($playerId === null) {
            return null;
        }

        $player = $this->playerRepository->find($playerId);
        if ($player === null || $player->getUser()->getId() !== $user->getId()) {
            return null;
        }

        return $player;
    }

    private function runGameEngine(?Player $player): void
    {
        try {
            $this->gameEngine->run($player);
        } catch (\Exception $exception) {
            // Do nothing...
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
