<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use SortDirection;

final class DoctrineUserRepository implements UserRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <User>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(User::class);
    }

    #[\Override]
    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * @param 'signup'|'lastSeen' $sort
     * @param 'asc'|'desc' $direction
     * @return User[]
     */
    #[\Override]
    public function findForAdminList(
        string $filter,
        string $sort,
        string $direction,
        int $limit,
        int $offset
    ): array {
        $field = $sort === 'lastSeen' ? 'u.lastSeenAt' : 'u.signedUpAt';
        $order = $direction === 'asc' ? SortDirection::Ascending : SortDirection::Descending;

        return $this->createAdminListQueryBuilder($filter)
            ->orderBy($field, $order)
            ->addOrderBy('u.id', $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function countForAdminList(string $filter): int
    {
        return (int) $this->createAdminListQueryBuilder($filter)
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createAdminListQueryBuilder(string $filter): QueryBuilder
    {
        $queryBuilder = $this->repository->createQueryBuilder('u');

        match ($filter) {
            'banned' => $queryBuilder->andWhere('u.banned = true'),
            'email-verified' => $queryBuilder->andWhere('u.emailVerified = true'),
            'email-unverified' => $queryBuilder->andWhere('u.emailVerified = false'),
            default => null,
        };

        return $queryBuilder;
    }

    #[\Override]
    public function findByEmailVerificationToken(string $emailVerificationToken): ?User
    {
        return $this->repository->findOneBy(['emailVerificationToken' => $emailVerificationToken]);
    }

    #[\Override]
    public function findByPasswordResetToken(string $passwordResetToken): ?User
    {
        return $this->repository->findOneBy(['passwordResetToken' => $passwordResetToken]);
    }

    #[\Override]
    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    #[\Override]
    public function findByUsername(string $username): ?User
    {
        return $this->repository->findOneBy(['username' => $username]);
    }

    /**
     * @param DateTime $firstDateTime
     * @param DateTime $lastDateTime
     * @return User[]
     */
    #[\Override]
    public function findByLastSeenBetween(DateTime $firstDateTime, DateTime $lastDateTime): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.lastSeenAt BETWEEN :firstDateTime AND :lastDateTime')
            ->setParameter('firstDateTime', $firstDateTime)
            ->setParameter('lastDateTime', $lastDateTime)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function loadUserByUsername(string $username): ?User
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    #[\Override]
    public function removeUnverifiedSignedUpBefore(DateTime $dateTime): int
    {
        $removed = $this->entityManager->createQuery(
            'DELETE FROM ' . User::class . ' u
             WHERE u.emailVerified = false
             AND u.signedUpAt < :dateTime
             AND NOT EXISTS (SELECT p FROM ' . Player::class . ' p WHERE p.user = u)
             AND NOT EXISTS (SELECT t FROM ' . Topic::class . ' t WHERE t.user = u OR t.editUser = u)
             AND NOT EXISTS (SELECT po FROM ' . Post::class . ' po WHERE po.user = u OR po.editUser = u)
             AND NOT EXISTS (SELECT ur FROM ' . UnbanRequest::class . ' ur WHERE ur.user = u)
             AND NOT EXISTS (SELECT l FROM ' . UserIpLog::class . ' l WHERE l.user = u)'
        )
            ->setParameter('dateTime', $dateTime)
            ->execute();

        return $removed;
    }

    #[\Override]
    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
