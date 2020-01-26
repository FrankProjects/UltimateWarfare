<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Repository\OperationRepository;

final class DoctrineOperationRepository implements OperationRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Operation::class);
    }

    /**
     * @return Operation[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
