<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;

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
     * @return User[]
     */
    #[\Override]
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @return User[]
     */
    #[\Override]
    public function findAllEmailUnverified(): array
    {
        return $this->repository->findBy(['emailVerified' => 0]);
    }

    /**
     * @return User[]
     */
    #[\Override]
    public function findAllBanned(): array
    {
        return $this->repository->findBy(['banned' => 1]);
    }

    /**
     * @return User[]
     */
    #[\Override]
    public function findAllEmailVerified(): array
    {
        return $this->repository->findBy(['emailVerified' => 1]);
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

    /**
     * @return User[]
     */
    #[\Override]
    public function findByLastSeenIp(string $lastSeenIp): array
    {
        return $this->repository->findBy(['lastSeenIp' => $lastSeenIp]);
    }

    #[\Override]
    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
