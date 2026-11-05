<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;

/**
 * Records that a user made a request: updates User::lastSeenAt and the user's IP timeline (UserIpLog).
 * Timestamps are only written every WRITE_INTERVAL_SECONDS, so most requests don't write anything.
 */
final class UserActivityUpdaterService
{
    /** A request after this many seconds without activity starts a new UserIpLog row, even on the same IP */
    public const int SESSION_GAP_SECONDS = 30 * 60;
    public const int WRITE_INTERVAL_SECONDS = 5 * 60;

    private UserRepository $userRepository;
    private UserIpLogRepository $userIpLogRepository;

    public function __construct(
        UserRepository $userRepository,
        UserIpLogRepository $userIpLogRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userIpLogRepository = $userIpLogRepository;
    }

    public function updateActivity(User $user, ?string $ip, DateTime $now): void
    {
        if ($ip !== null && $ip !== '') {
            $this->updateIpLog($user, $ip, $now);
        }

        $lastSeenAt = $user->getLastSeenAt();
        if ($lastSeenAt === null || $this->secondsBetween($lastSeenAt, $now) >= self::WRITE_INTERVAL_SECONDS) {
            $user->setLastSeenAt($now);
            $this->userRepository->save($user);
        }
    }

    private function updateIpLog(User $user, string $ip, DateTime $now): void
    {
        $latest = $this->userIpLogRepository->findLatestByUser($user);

        if (
            $latest === null
            || $latest->getIp() !== $ip
            || $this->secondsBetween($latest->getLastSeenAt(), $now) > self::SESSION_GAP_SECONDS
        ) {
            $this->userIpLogRepository->save(UserIpLog::create($user, $ip, $now));
            return;
        }

        if ($this->secondsBetween($latest->getLastSeenAt(), $now) >= self::WRITE_INTERVAL_SECONDS) {
            $latest->setLastSeenAt($now);
            $this->userIpLogRepository->save($latest);
        }
    }

    private function secondsBetween(DateTime $from, DateTime $to): int
    {
        return $to->getTimestamp() - $from->getTimestamp();
    }
}
