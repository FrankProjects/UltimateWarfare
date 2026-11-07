<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Service\MultiAccountDetection\MultiAccountPair;

/**
 * Finds pairs of users that may be the same person, from the IP log: sessions on the same IP address that
 * overlap in time (both accounts online from one connection) or follow each other within the session gap
 * (logging out of one account and into the other). Only a hint for an admin to review: households, schools
 * and mobile networks share IP addresses too, so nobody is banned automatically.
 */
final class MultiAccountDetectionService
{
    private UserIpLogRepository $userIpLogRepository;

    public function __construct(UserIpLogRepository $userIpLogRepository)
    {
        $this->userIpLogRepository = $userIpLogRepository;
    }

    /**
     * Most suspicious first: most overlapping sessions, then most adjacent sessions.
     *
     * @return MultiAccountPair[]
     */
    public function findPairs(): array
    {
        $pairs = [];
        $sessionPairs = $this->userIpLogRepository->findSessionPairsOnSameIp(
            UserActivityUpdaterService::SESSION_GAP_SECONDS
        );

        foreach ($sessionPairs as [$sessionA, $sessionB]) {
            $key = $sessionA->getUser()->getId() . '-' . $sessionB->getUser()->getId();
            $pairs[$key] ??= new MultiAccountPair($sessionA->getUser(), $sessionB->getUser());

            $overlapping = $sessionA->getFirstSeenAt() <= $sessionB->getLastSeenAt()
                && $sessionB->getFirstSeenAt() <= $sessionA->getLastSeenAt();
            $sharedAt = max($sessionA->getLastSeenAt(), $sessionB->getLastSeenAt());

            $pairs[$key]->addSessionPair($sessionA->getIp(), $overlapping, $sharedAt);
        }

        usort(
            $pairs,
            static fn (MultiAccountPair $a, MultiAccountPair $b): int =>
                [$b->getOverlappingSessions(), $b->getAdjacentSessions()]
                <=> [$a->getOverlappingSessions(), $a->getAdjacentSessions()]
        );

        return $pairs;
    }
}
