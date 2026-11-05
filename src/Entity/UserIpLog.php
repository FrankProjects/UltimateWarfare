<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use DateTime;

/**
 * One continuous stretch of activity of a user from one IP address. A new row starts when the IP changes
 * or after a gap without requests (see UserActivityUpdaterService), so the rows form the user's IP timeline.
 * Used to detect multiple accounts; rows are deleted by game:privacy:cleanup after the retention period.
 */
class UserIpLog
{
    private int $id;
    private User $user;
    private string $ip;
    private DateTime $firstSeenAt;
    private DateTime $lastSeenAt;

    public static function create(User $user, string $ip, DateTime $seenAt): self
    {
        $userIpLog = new self();
        $userIpLog->user = $user;
        $userIpLog->ip = $ip;
        $userIpLog->firstSeenAt = clone $seenAt;
        $userIpLog->lastSeenAt = clone $seenAt;

        return $userIpLog;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getFirstSeenAt(): DateTime
    {
        return $this->firstSeenAt;
    }

    public function getLastSeenAt(): DateTime
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(DateTime $lastSeenAt): void
    {
        $this->lastSeenAt = clone $lastSeenAt;
    }
}
