<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;

final class DoctrineUserIpLogRepository implements UserIpLogRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[\Override]
    public function findLatestByUser(User $user): ?UserIpLog
    {
        $result = $this->entityManager->createQuery(
            'SELECT l FROM ' . UserIpLog::class . ' l
             WHERE l.user = :user
             ORDER BY l.lastSeenAt DESC, l.id DESC'
        )
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        assert($result instanceof UserIpLog || $result === null);

        return $result;
    }

    /**
     * @return UserIpLog[]
     */
    #[\Override]
    public function findByUser(User $user, ?int $limit = null): array
    {
        return $this->entityManager->createQuery(
            'SELECT l FROM ' . UserIpLog::class . ' l
             WHERE l.user = :user
             ORDER BY l.lastSeenAt DESC, l.id DESC'
        )
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @return UserIpLog[]
     */
    #[\Override]
    public function findOfOtherUsersSharingIpWith(User $user): array
    {
        return $this->entityManager->createQuery(
            'SELECT other, u FROM ' . UserIpLog::class . ' other
             JOIN other.user u
             WHERE other.user != :user
             AND other.ip IN (SELECT own.ip FROM ' . UserIpLog::class . ' own WHERE own.user = :user)
             ORDER BY other.lastSeenAt DESC'
        )
            ->setParameter('user', $user)
            ->getResult();
    }

    /**
     * @return array<int, array{0: UserIpLog, 1: UserIpLog}>
     */
    #[\Override]
    public function findSessionPairsOnSameIp(int $maxGapSeconds): array
    {
        /** @var array<int, array{aId: int|string, bId: int|string}> $idPairs */
        $idPairs = $this->entityManager->createQuery(
            'SELECT a.id AS aId, b.id AS bId FROM ' . UserIpLog::class . ' a
             JOIN ' . UserIpLog::class . ' b WITH b.ip = a.ip AND IDENTITY(b.user) > IDENTITY(a.user)
             WHERE a.firstSeenAt <= DATE_ADD(b.lastSeenAt, :maxGap, \'second\')
             AND b.firstSeenAt <= DATE_ADD(a.lastSeenAt, :maxGap, \'second\')'
        )
            ->setParameter('maxGap', $maxGapSeconds)
            ->getScalarResult();

        if ($idPairs === []) {
            return [];
        }

        $ids = array_unique(array_merge(array_column($idPairs, 'aId'), array_column($idPairs, 'bId')));

        /** @var UserIpLog[] $userIpLogs */
        $userIpLogs = $this->entityManager->createQuery(
            'SELECT l, u FROM ' . UserIpLog::class . ' l JOIN l.user u WHERE l.id IN (:ids)'
        )
            ->setParameter('ids', $ids)
            ->getResult();

        $userIpLogsById = [];
        foreach ($userIpLogs as $userIpLog) {
            $userIpLogsById[$userIpLog->getId()] = $userIpLog;
        }

        $sessionPairs = [];
        foreach ($idPairs as $idPair) {
            $sessionPairs[] = [$userIpLogsById[(int) $idPair['aId']], $userIpLogsById[(int) $idPair['bId']]];
        }

        return $sessionPairs;
    }

    #[\Override]
    public function save(UserIpLog $userIpLog): void
    {
        $this->entityManager->persist($userIpLog);
        $this->entityManager->flush();
    }

    #[\Override]
    public function removeByUser(User $user): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM ' . UserIpLog::class . ' l WHERE l.user = :user'
        )
            ->setParameter('user', $user)
            ->execute();
    }

    #[\Override]
    public function removeLastSeenBefore(DateTime $dateTime): int
    {
        $removed = $this->entityManager->createQuery(
            'DELETE FROM ' . UserIpLog::class . ' l WHERE l.lastSeenAt < :dateTime'
        )
            ->setParameter('dateTime', $dateTime)
            ->execute();

        return $removed;
    }
}
