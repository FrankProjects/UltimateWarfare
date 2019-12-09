<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;

final class DoctrineWorldSectorRepository implements WorldSectorRepository
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
     * DoctrineWorldSectorRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldSector::class);
    }

    /**
     * @param int $id
     * @param World $world
     * @return WorldSector|null
     */
    public function findByIdAndWorld(int $id, World $world): ?WorldSector
    {
        return $this->repository->findOneBy(['id' => $id, 'world' => $world]);
    }

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     * @return WorldSector|null
     */
    public function findByWorldXY(World $world, int $x, int $y): ?WorldSector
    {
        return $this->repository->findOneBy(['world' => $world, 'x' => $x, 'y' => $y]);
    }

    /**
     * @param WorldSector $worldSector
     */
    public function save(WorldSector $worldSector): void
    {
        $this->entityManager->persist($worldSector);
        $this->entityManager->flush();
    }
}
