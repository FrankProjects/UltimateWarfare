<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\FederationApplication;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\FederationApplicationRepository;

final class DoctrineFederationApplicationRepository implements FederationApplicationRepository
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
     * DoctrineFederationApplicationRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(FederationApplication::class);
    }

    /**
     * @param int $id
     * @param World $world
     * @return FederationApplication|null
     */
    public function findByIdAndWorld(int $id, World $world): ?FederationApplication
    {
        return $this->repository->findOneBy(['id' => $id, 'world' => $world]);
    }

    /**
     * @param FederationApplication $federationApplication
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(FederationApplication $federationApplication): void
    {
        $this->entityManager->remove($federationApplication);
        $this->entityManager->flush();
    }

    /**
     * @param FederationApplication $federationApplication
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(FederationApplication $federationApplication): void
    {
        $this->entityManager->persist($federationApplication);
        $this->entityManager->flush();
    }
}
