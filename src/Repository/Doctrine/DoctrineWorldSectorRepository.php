<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;

final class DoctrineWorldSectorRepository implements WorldSectorRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldSector::class);
    }

    public function findByIdAndWorld(int $id, World $world): ?WorldSector
    {
        return $this->repository->findOneBy(['id' => $id, 'world' => $world]);
    }

    public function findByWorldXY(World $world, int $x, int $y): ?WorldSector
    {
        return $this->repository->findOneBy(['world' => $world, 'x' => $x, 'y' => $y]);
    }

    public function save(WorldSector $worldSector): void
    {
        $this->entityManager->persist($worldSector);
        $this->entityManager->flush();
    }

    public function refresh(WorldSector $worldSector): void
    {
        $this->entityManager->refresh($worldSector);
    }
}
