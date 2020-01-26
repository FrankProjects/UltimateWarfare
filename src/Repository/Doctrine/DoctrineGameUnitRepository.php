<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRepository;

final class DoctrineGameUnitRepository implements GameUnitRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameUnit::class);
    }

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

    public function save(GameUnit $gameUnit): void
    {
        $this->entityManager->persist($gameUnit);
        $this->entityManager->flush();
    }
}
