<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;

final class DoctrineGameUnitTypeRepository implements GameUnitTypeRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameUnitType::class);
    }

    public function find(int $id): ?GameUnitType
    {
        return $this->repository->find($id);
    }

    /**
     * @return GameUnitType[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function save(GameUnitType $gameUnitType): void
    {
        $this->entityManager->persist($gameUnitType);
        $this->entityManager->flush();
    }
}
