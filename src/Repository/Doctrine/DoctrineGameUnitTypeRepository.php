<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Exception\GameUnitTypeNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;

final class DoctrineGameUnitTypeRepository implements GameUnitTypeRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <GameUnitType>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameUnitType::class);
    }

    /**
     * @throws GameUnitTypeNotFoundException
     */
    public function find(int $id): GameUnitType
    {
        $gameUnitType = $this->repository->find($id);
        if ($gameUnitType === null) {
            throw new GameUnitTypeNotFoundException();
        }
        return $gameUnitType;
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
