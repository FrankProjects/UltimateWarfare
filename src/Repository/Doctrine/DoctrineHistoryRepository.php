<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\History;
use FrankProjects\UltimateWarfare\Repository\HistoryRepository;

final class DoctrineHistoryRepository implements HistoryRepository
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
     * DoctrineHistoryRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(History::class);
    }

    /**
     * @return History[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param History $history
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(History $history): void
    {
        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
