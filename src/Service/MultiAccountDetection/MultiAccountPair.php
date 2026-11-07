<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\MultiAccountDetection;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;

/**
 * Two users that used the same IP address close together in time, see MultiAccountDetectionService.
 */
final class MultiAccountPair
{
    /** Sessions on the same IP at the same time: both accounts were online from one connection */
    private int $overlappingSessions = 0;
    /** Sessions on the same IP right after each other: switching between accounts */
    private int $adjacentSessions = 0;
    /** @var array<string, true> */
    private array $sharedIps = [];
    private ?DateTime $lastSharedAt = null;

    public function __construct(
        public readonly User $userA,
        public readonly User $userB
    ) {
    }

    public function addSessionPair(string $ip, bool $overlapping, DateTime $sharedAt): void
    {
        if ($overlapping) {
            $this->overlappingSessions++;
        } else {
            $this->adjacentSessions++;
        }

        $this->sharedIps[$ip] = true;
        if ($this->lastSharedAt === null || $sharedAt > $this->lastSharedAt) {
            $this->lastSharedAt = $sharedAt;
        }
    }

    public function getOverlappingSessions(): int
    {
        return $this->overlappingSessions;
    }

    public function getAdjacentSessions(): int
    {
        return $this->adjacentSessions;
    }

    /**
     * @return array<int, string>
     */
    public function getSharedIps(): array
    {
        return array_map('strval', array_keys($this->sharedIps));
    }

    public function getLastSharedAt(): ?DateTime
    {
        return $this->lastSharedAt;
    }
}
