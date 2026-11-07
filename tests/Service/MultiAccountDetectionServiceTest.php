<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Service\MultiAccountDetectionService;
use PHPUnit\Framework\TestCase;

class MultiAccountDetectionServiceTest extends TestCase
{
    public function testSessionPairsAreGroupedPerUserPairAndClassified(): void
    {
        $alice = $this->createUser(1, 'alice');
        $bob = $this->createUser(2, 'bob');

        $pairs = $this->createService([
            // Online at the same time
            [
                $this->createSession($alice, '10.0.0.1', '2026-11-01 10:00', '2026-11-01 11:00'),
                $this->createSession($bob, '10.0.0.1', '2026-11-01 10:30', '2026-11-01 12:00'),
            ],
            // Bob logged in 10 minutes after Alice's session ended
            [
                $this->createSession($alice, '10.0.0.2', '2026-11-02 10:00', '2026-11-02 11:00'),
                $this->createSession($bob, '10.0.0.2', '2026-11-02 11:10', '2026-11-02 12:00'),
            ],
        ])->findPairs();

        self::assertCount(1, $pairs);
        self::assertSame($alice, $pairs[0]->userA);
        self::assertSame($bob, $pairs[0]->userB);
        self::assertSame(1, $pairs[0]->getOverlappingSessions());
        self::assertSame(1, $pairs[0]->getAdjacentSessions());
        self::assertSame(['10.0.0.1', '10.0.0.2'], $pairs[0]->getSharedIps());
        self::assertEquals(new DateTime('2026-11-02 12:00'), $pairs[0]->getLastSharedAt());
    }

    public function testPairsAreSortedByOverlappingThenAdjacentSessions(): void
    {
        $alice = $this->createUser(1, 'alice');
        $bob = $this->createUser(2, 'bob');
        $carol = $this->createUser(3, 'carol');
        $dave = $this->createUser(4, 'dave');

        $pairs = $this->createService([
            // alice + bob: 1 adjacent
            [
                $this->createSession($alice, '10.0.0.1', '2026-11-01 10:00', '2026-11-01 11:00'),
                $this->createSession($bob, '10.0.0.1', '2026-11-01 11:05', '2026-11-01 12:00'),
            ],
            // carol + dave: 1 overlapping
            [
                $this->createSession($carol, '10.0.0.2', '2026-11-01 10:00', '2026-11-01 11:00'),
                $this->createSession($dave, '10.0.0.2', '2026-11-01 10:30', '2026-11-01 10:45'),
            ],
        ])->findPairs();

        self::assertCount(2, $pairs);
        self::assertSame($carol, $pairs[0]->userA);
        self::assertSame($alice, $pairs[1]->userA);
    }

    public function testNoSessionPairsGiveNoPairs(): void
    {
        self::assertSame([], $this->createService([])->findPairs());
    }

    /**
     * @param array<int, array{0: UserIpLog, 1: UserIpLog}> $sessionPairs
     */
    private function createService(array $sessionPairs): MultiAccountDetectionService
    {
        $userIpLogRepository = self::createStub(UserIpLogRepository::class);
        $userIpLogRepository->method('findSessionPairsOnSameIp')->willReturn($sessionPairs);

        return new MultiAccountDetectionService($userIpLogRepository);
    }

    private function createUser(int $id, string $username): User
    {
        $user = new User();
        $user->setId($id);
        $user->setUsername($username);

        return $user;
    }

    private function createSession(User $user, string $ip, string $firstSeenAt, string $lastSeenAt): UserIpLog
    {
        $session = UserIpLog::create($user, $ip, new DateTime($firstSeenAt));
        $session->setLastSeenAt(new DateTime($lastSeenAt));

        return $session;
    }
}
