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
    public function findByUser(User $user, int $limit): array;

    /**
     * Log rows of other users that used any IP address the user used, most recent first.
     *
     * @return UserIpLog[]
     */
    public function findOfOtherUsersSharingIpWith(User $user): array;

    public function save(UserIpLog $userIpLog): void;

    /**
     * @return int Number of removed rows
     */
    public function removeLastSeenBefore(DateTime $dateTime): int;
}
