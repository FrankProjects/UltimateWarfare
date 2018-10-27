<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Repository\GameResourceRepository;

final class DoctrineGameResourceRepository implements GameResourceRepository
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
     * GameResourceRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameResource::class);
    }

    /**
     * @param int $id
     * @return GameResource|null
     */
    public function find(int $id): ?GameResource
    {
        return $this->repository->find($id);
    }

    /**
     * @return GameResource[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
