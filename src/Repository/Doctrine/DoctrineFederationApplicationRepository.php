<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\FederationApplication;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\FederationApplicationRepository;

final class DoctrineFederationApplicationRepository implements FederationApplicationRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <FederationApplication>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(FederationApplication::class);
    }

    public function find(int $id): ?FederationApplication
    {
        return $this->repository->find($id);
    }

    public function remove(FederationApplication $federationApplication): void
    {
        $this->entityManager->remove($federationApplication);
        $this->entityManager->flush();
    }

    public function save(FederationApplication $federationApplication): void
    {
        $this->entityManager->persist($federationApplication);
        $this->entityManager->flush();
    }
}
