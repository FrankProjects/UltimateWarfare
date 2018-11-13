<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;

final class DoctrineFederationNewsRepository implements FederationNewsRepository
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
     * DoctrineFederationNewsRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(FederationNews::class);
    }

    /**
     * @param Federation $federation
     * @return FederationNews[]
     */
    public function findByFederationSortedByTimestamp(Federation $federation): array
    {
        return $this->repository->findBy(['federation' => $federation], ['timestamp' => 'DESC']);
    }

    /**
     * @param FederationNews $federationNews
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(FederationNews $federationNews): void
    {
        $this->entityManager->remove($federationNews);
        $this->entityManager->flush();
    }

    /**
     * @param FederationNews $federationNews
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(FederationNews $federationNews): void
    {
        $this->entityManager->persist($federationNews);
        $this->entityManager->flush();
    }
}
