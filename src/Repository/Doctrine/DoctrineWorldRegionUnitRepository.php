<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;

final class DoctrineWorldRegionUnitRepository implements WorldRegionUnitRepository
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
     * DoctrineWorldRegionUnitRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegionUnit::class);
    }

    /**
     * @param int $id
     * @return WorldRegionUnit|null
     */
    public function find(int $id): ?WorldRegionUnit
    {
        return $this->repository->find($id);
    }

    /**
     * @param WorldRegionUnit $worldRegionUnit
     */
    public function remove(WorldRegionUnit $worldRegionUnit): void
    {
        $this->entityManager->remove($worldRegionUnit);
        $this->entityManager->flush();
    }

    /**
     * @param WorldRegionUnit $worldRegionUnit
     */
    public function save(WorldRegionUnit $worldRegionUnit): void
    {
        $this->entityManager->persist($worldRegionUnit);
        $this->entityManager->flush();
    }
}
