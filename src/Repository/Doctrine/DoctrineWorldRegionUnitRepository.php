<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;

final class DoctrineWorldRegionUnitRepository implements WorldRegionUnitRepository
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
     * WorldRegionUnitRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegionUnit::class);
    }

    /**
     * @param int $id
     * @return WorldRegionUnit|null
     */
    public function find(int $id): ?WorldRegionUnit
    {
        return $this->repository->find($id);
    }

    /**
     * @param WorldRegionUnit $worldRegionUnit
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(WorldRegionUnit $worldRegionUnit): void
    {
        $this->entityManager->remove($worldRegionUnit);
        $this->entityManager->flush();
    }

    /**
     * @param WorldRegionUnit $worldRegionUnit
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(WorldRegionUnit $worldRegionUnit): void
    {
        $this->entityManager->persist($worldRegionUnit);
        $this->entityManager->flush();
    }
}
