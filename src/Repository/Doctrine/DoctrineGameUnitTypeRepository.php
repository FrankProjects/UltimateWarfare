<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;

final class DoctrineGameUnitTypeRepository implements GameUnitTypeRepository
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
     * GameUnitTypeRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameUnitType::class);
    }

    /**
     * @param int $id
     * @return GameUnitType|null
     */
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

    /**
     * @param GameUnitType $gameUnitType
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(GameUnitType $gameUnitType): void
    {
        $this->entityManager->persist($gameUnitType);
        $this->entityManager->flush();
    }
}
