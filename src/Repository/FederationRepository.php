<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Federation;

final class FederationRepository implements FederationRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * FederationRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Federation::class);
    }

    /**
     * @param int $id
     * @return Federation|null
     */
    public function find(int $id): ?Federation
    {
        return $this->repository->find($id);
    }

    /**
     * @param Federation $federation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Federation $federation): void
    {
        $this->entityManager->persist($federation);
        $this->entityManager->flush();
    }
}
