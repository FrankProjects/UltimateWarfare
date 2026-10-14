<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Requests of a player that change state (POST, DELETE, ...) run in one database transaction that holds a row
 * lock on the current player from the start of the request until the response: concurrent requests of the player
 * can't read the same resources and overwrite each other's changes. The transaction commits for successful
 * responses (status < 400) and rolls back for error responses and exceptions, so a failed action never leaves
 * half of its changes behind. Actions that also change another player or shared row lock those explicitly
 * (PlayerStateUpdaterService::updateStateForPlayer(), EntityLockRepository::lockForUpdate()).
 *
 * Safe requests (GET) only read: they don't take the lock, run in parallel and don't update player state.
 * Completed construction and research are processed by the queue worker (game:queue:work);
 * the state update at the start of a state-changing request completes the player's own due items in any case.
 */
final class PlayerRequestSubscriber extends AbstractUserSubscriber implements EventSubscriberInterface
{
    private PlayerStateUpdaterService $playerStateUpdaterService;
    private RequestStack $requestStack;
    private PlayerRepository $playerRepository;
    private EntityManagerInterface $entityManager;
    private bool $transactionStarted = false;

    public function __construct(
        PlayerStateUpdaterService $playerStateUpdaterService,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($tokenStorage);
        $this->playerStateUpdaterService = $playerStateUpdaterService;
        $this->requestStack = $requestStack;
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Safe requests only read: resources are projected for display and the queue worker completes queue items
        if (!$event->isMainRequest() || $event->getRequest()->isMethodSafe()) {
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

        $this->entityManager->beginTransaction();
        $this->transactionStarted = true;

        // Nested in the request transaction: the row lock on the player is held until the response
        $this->playerStateUpdaterService->updateStateForPlayer($player, time());
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

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
