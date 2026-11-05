<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\UserActivityUpdaterService;
use PHPUnit\Framework\TestCase;

class UserActivityUpdaterServiceTest extends TestCase
{
    private const string NOW = '2026-11-05 12:00:00';

    /** @var UserIpLog[] */
    private array $savedIpLogs = [];
    private int $userSaves = 0;

    public function testFirstRequestCreatesIpLogAndSetsLastSeen(): void
    {
        $user = new User();

        $this->createService(null)->updateActivity($user, '10.0.0.1', new DateTime(self::NOW));

        self::assertCount(1, $this->savedIpLogs);
        self::assertSame('10.0.0.1', $this->savedIpLogs[0]->getIp());
        self::assertSame($user, $this->savedIpLogs[0]->getUser());
        self::assertEquals(new DateTime(self::NOW), $this->savedIpLogs[0]->getFirstSeenAt());
        self::assertEquals(new DateTime(self::NOW), $user->getLastSeenAt());
        self::assertSame(1, $this->userSaves);
    }

    public function testRecentRequestFromSameIpWritesNothing(): void
    {
        $user = new User();
        $user->setLastSeenAt(new DateTime('2026-11-05 11:58:00'));
        $latest = UserIpLog::create($user, '10.0.0.1', new DateTime('2026-11-05 11:58:00'));

        $this->createService($latest)->updateActivity($user, '10.0.0.1', new DateTime(self::NOW));

        self::assertSame([], $this->savedIpLogs);
        self::assertSame(0, $this->userSaves);
        self::assertEquals(new DateTime('2026-11-05 11:58:00'), $latest->getLastSeenAt());
    }

    public function testRequestFromSameIpAfterWriteIntervalExtendsIpLog(): void
    {
        $user = new User();
        $user->setLastSeenAt(new DateTime('2026-11-05 11:50:00'));
        $latest = UserIpLog::create($user, '10.0.0.1', new DateTime('2026-11-05 11:50:00'));

        $this->createService($latest)->updateActivity($user, '10.0.0.1', new DateTime(self::NOW));

        self::assertSame([$latest], $this->savedIpLogs);
        self::assertEquals(new DateTime('2026-11-05 11:50:00'), $latest->getFirstSeenAt());
        self::assertEquals(new DateTime(self::NOW), $latest->getLastSeenAt());
        self::assertEquals(new DateTime(self::NOW), $user->getLastSeenAt());
        self::assertSame(1, $this->userSaves);
    }

    public function testRequestFromOtherIpStartsNewIpLog(): void
    {
        $user = new User();
        $user->setLastSeenAt(new DateTime('2026-11-05 11:59:00'));
        $latest = UserIpLog::create($user, '10.0.0.1', new DateTime('2026-11-05 11:59:00'));

        $this->createService($latest)->updateActivity($user, '10.0.0.2', new DateTime(self::NOW));

        self::assertCount(1, $this->savedIpLogs);
        self::assertNotSame($latest, $this->savedIpLogs[0]);
        self::assertSame('10.0.0.2', $this->savedIpLogs[0]->getIp());
        self::assertEquals(new DateTime('2026-11-05 11:59:00'), $latest->getLastSeenAt());
    }

    public function testRequestFromSameIpAfterSessionGapStartsNewIpLog(): void
    {
        $user = new User();
        $user->setLastSeenAt(new DateTime('2026-11-05 11:00:00'));
        $latest = UserIpLog::create($user, '10.0.0.1', new DateTime('2026-11-05 11:00:00'));

        $this->createService($latest)->updateActivity($user, '10.0.0.1', new DateTime(self::NOW));

        self::assertCount(1, $this->savedIpLogs);
        self::assertNotSame($latest, $this->savedIpLogs[0]);
        self::assertEquals(new DateTime('2026-11-05 11:00:00'), $latest->getLastSeenAt());
    }

    public function testRequestWithoutIpOnlyUpdatesLastSeen(): void
    {
        $user = new User();

        $this->createService(null)->updateActivity($user, null, new DateTime(self::NOW));

        self::assertSame([], $this->savedIpLogs);
        self::assertEquals(new DateTime(self::NOW), $user->getLastSeenAt());
    }

    private function createService(?UserIpLog $latest): UserActivityUpdaterService
    {
        $userRepository = self::createStub(UserRepository::class);
        $userRepository->method('save')->willReturnCallback(function (): void {
            $this->userSaves++;
        });

        $userIpLogRepository = self::createStub(UserIpLogRepository::class);
        $userIpLogRepository->method('findLatestByUser')->willReturn($latest);
        $userIpLogRepository->method('save')->willReturnCallback(function (UserIpLog $userIpLog): void {
            $this->savedIpLogs[] = $userIpLog;
        });

        return new UserActivityUpdaterService($userRepository, $userIpLogRepository);
    }
}
