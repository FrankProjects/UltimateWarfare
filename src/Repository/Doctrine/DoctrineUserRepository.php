<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use DateTime;
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

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * @return User[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @return User[]
     */
    public function findAllDisabled(): array
    {
        return $this->repository->findBy(['enabled' => 0]);
    }

    /**
     * @return User[]
     */
    public function findAllBanned(): array
    {
        return $this->repository->findBy(['active' => 0]);
    }

    /**
     * @return User[]
     */
    public function findAllActive(): array
    {
        return $this->repository->findBy(['enabled' => 1]);
    }

    public function findByConfirmationToken(string $confirmationToken): ?User
    {
        return $this->repository->findOneBy(['confirmationToken' => $confirmationToken]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->repository->findOneBy(['username' => $username]);
    }

    /**
     * @param DateTime $firstDateTime
     * @param DateTime $lastDateTime
     * @return User[]
     */
    public function findByLastLogin(DateTime $firstDateTime, DateTime $lastDateTime): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.lastLogin BETWEEN :firstDateTime AND :lastDateTime')
            ->setParameter('firstDateTime', $firstDateTime)
            ->setParameter('lastDateTime', $lastDateTime)
            ->getQuery()
            ->getResult();
    }

    public function loadUserByUsername(string $username): ?User
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
