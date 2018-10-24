<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnit;

final class GameUnitRepository implements GameUnitRepositoryInterface
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
     * GameUnitRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameUnit::class);
    }

    /**
     * @param int $id
     * @return GameUnit|null
     */
    public function find(int $id): ?GameUnit
    {
        return $this->repository->find($id);
    }

    /**
     * @return GameUnit[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param GameUnit $gameUnit
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(GameUnit $gameUnit): void
    {
        $this->entityManager->persist($gameUnit);
        $this->entityManager->flush();
    }
}
