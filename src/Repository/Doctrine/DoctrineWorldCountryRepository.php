<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\WorldCountry;
use FrankProjects\UltimateWarfare\Repository\WorldCountryRepository;

final class DoctrineWorldCountryRepository implements WorldCountryRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * DoctrineWorldCountryRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldCountry::class);
    }

    /**
     * @param int $id
     * @return WorldCountry|null
     */
    public function find(int $id): ?WorldCountry
    {
        return $this->repository->find($id);
    }
}
