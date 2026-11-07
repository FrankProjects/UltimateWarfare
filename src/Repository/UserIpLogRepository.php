<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;

interface UserIpLogRepository
{
    public function findLatestByUser(User $user): ?UserIpLog;

    /**
     * The user's IP timeline, most recent first.
     *
     * @return UserIpLog[]
     */
    public function findByUser(User $user, ?int $limit = null): array;

    /**
     * Log rows of other users that used any IP address the user used, most recent first.
     *
     * @return UserIpLog[]
     */
    public function findOfOtherUsersSharingIpWith(User $user): array;

    /**
     * Pairs of sessions of two different users on the same IP address that overlap in time or are at most
     * $maxGapSeconds apart. The session of the user with the lowest id comes first.
     *
     * @return array<int, array{0: UserIpLog, 1: UserIpLog}>
     */
    public function findSessionPairsOnSameIp(int $maxGapSeconds): array;

    public function save(UserIpLog $userIpLog): void;

    public function removeByUser(User $user): void;

    /**
     * @return int Number of removed rows
     */
    public function removeLastSeenBefore(DateTime $dateTime): int;
}
