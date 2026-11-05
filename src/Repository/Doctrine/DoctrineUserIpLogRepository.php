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
    public function findByUser(User $user, int $limit): array
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

    #[\Override]
    public function save(UserIpLog $userIpLog): void
    {
        $this->entityManager->persist($userIpLog);
        $this->entityManager->flush();
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
