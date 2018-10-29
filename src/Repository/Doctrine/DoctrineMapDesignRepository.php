<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\MapDesign;
use FrankProjects\UltimateWarfare\Repository\MapDesignRepository;

final class DoctrineMapDesignRepository implements MapDesignRepository
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
     * DoctrineMapDesignRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(MapDesign::class);
    }

    /**
     * @param int $id
     * @return MapDesign|null
     */
    public function find(int $id): ?MapDesign
    {
        return $this->repository->find($id);
    }

    /**
     * @return MapDesign[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param MapDesign $mapDesign
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(MapDesign $mapDesign): void
    {
        $this->entityManager->persist($mapDesign);
        $this->entityManager->flush();
    }
}
