<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

final class WorldRegionRepository implements WorldRegionRepositoryInterface
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
     * WorldRegionRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegion::class);
    }

    /**
     * @param int $id
     * @return WorldRegion|null
     */
    public function find(int $id): ?WorldRegion
    {
        return $this->repository->find($id);
    }

    /**
     * @param WorldRegion $worldRegion
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(WorldRegion $worldRegion): void
    {
        $this->entityManager->persist($worldRegion);
        $this->entityManager->flush();
    }
}
