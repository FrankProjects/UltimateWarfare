<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\EventSubscriber;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\EventSubscriber\PlayerRequestSubscriber;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Tests\Service\CreatesPlayerStateUpdaterService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PlayerRequestSubscriberTest extends TestCase
{
    use CreatesPlayerStateUpdaterService;

    private EntityManagerInterface&MockObject $entityManager;
    private Player $player;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn(true);
        $this->entityManager->method('getConnection')->willReturn($connection);

        $user = new User();
        $user->setId(1);
        $this->player = new Player();
        $this->player->setId(1);
        $this->player->setUser($user);
        $this->player->setTimestampUpdate(time());
    }

    public function testStateChangingRequestLocksPlayerUntilSuccessfulResponseCommits(): void
    {
        $this->entityManager->expects(self::once())->method('refresh')
            ->with($this->player, LockMode::PESSIMISTIC_WRITE);
        // The request transaction plus the state update nested in it
        $this->entityManager->expects(self::exactly(2))->method('beginTransaction');
        $this->entityManager->expects(self::exactly(2))->method('commit');
        $this->entityManager->expects(self::never())->method('rollback');

        [$subscriber, $request] = $this->createSubscriber('POST');
        $subscriber->onKernelRequest($this->createRequestEvent($request));
        $subscriber->onKernelResponse($this->createResponseEvent($request, new Response('', 200)));
    }

    public function testErrorResponseRollsBack(): void
    {
        $this->entityManager->expects(self::once())->method('commit'); // only the nested state update
        $this->entityManager->expects(self::once())->method('rollback');

        [$subscriber, $request] = $this->createSubscriber('POST');
        $subscriber->onKernelRequest($this->createRequestEvent($request));
        $subscriber->onKernelResponse($this->createResponseEvent($request, new Response('', 400)));
    }

    public function testExceptionRollsBackOnlyOnce(): void
    {
        $this->entityManager->expects(self::once())->method('commit'); // only the nested state update
        $this->entityManager->expects(self::once())->method('rollback');

        [$subscriber, $request] = $this->createSubscriber('POST');
        $subscriber->onKernelRequest($this->createRequestEvent($request));
        $subscriber->onKernelException(new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new RuntimeException('Something went wrong')
        ));
        // The kernel still dispatches a response event for the error page
        $subscriber->onKernelResponse($this->createResponseEvent($request, new Response('', 500)));
    }

    public function testFailedCommitRollsBackAndRethrows(): void
    {
        $this->entityManager->method('commit')->willReturnOnConsecutiveCalls(
            null,
            $this->throwException(new RuntimeException('Deadlock found'))
        );
        $this->entityManager->expects(self::once())->method('rollback');

        [$subscriber, $request] = $this->createSubscriber('POST');
        $subscriber->onKernelRequest($this->createRequestEvent($request));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Deadlock found');
        $subscriber->onKernelResponse($this->createResponseEvent($request, new Response('', 200)));
    }

    public function testSafeRequestDoesNotStartATransaction(): void
    {
        $this->entityManager->expects(self::never())->method('beginTransaction');
        $this->entityManager->expects(self::never())->method('commit');
        $this->entityManager->expects(self::never())->method('rollback');

        [$subscriber, $request] = $this->createSubscriber('GET');
        $subscriber->onKernelRequest($this->createRequestEvent($request));
        $subscriber->onKernelResponse($this->createResponseEvent($request, new Response('', 200)));
    }

    /**
     * @return array{PlayerRequestSubscriber, Request}
     */
    private function createSubscriber(string $method): array
    {
        $request = Request::create('/game/api/test', $method);
        $session = new Session(new MockArraySessionStorage());
        $session->set('playerId', $this->player->getId());
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new UsernamePasswordToken($this->player->getUser(), 'main', []));

        $playerRepository = $this->createMock(PlayerRepository::class);
        $playerRepository->method('find')->willReturn($this->player);

        $subscriber = new PlayerRequestSubscriber(
            $this->createPlayerStateUpdaterService(null, null, $this->entityManager),
            $requestStack,
            $tokenStorage,
            $playerRepository,
            $this->entityManager
        );

        return [$subscriber, $request];
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    private function createResponseEvent(Request $request, Response $response): ResponseEvent
    {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );
    }
}
