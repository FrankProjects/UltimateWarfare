<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;

final class DoctrineWorldRepository implements WorldRepository
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
     * DoctrineWorldRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(World::class);
    }

    /**
     * @param int $id
     * @return World|null
     */
    public function find(int $id): ?World
    {
        return $this->repository->find($id);
    }

    /**
     * @return World[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param bool $public
     * @return World[]
     */
    public function findByPublic(bool $public): array
    {
        return $this->repository->findBy(['public' => $public]);
    }

    /**
     * XXX TODO: Improve with custom repository queries
     *
     * @param World $world
     */
    public function remove(World $world): void
    {
        foreach ($world->getWorldSectors() as $worldSector) {
            foreach ($worldSector->getWorldRegions() as $worldRegion) {
                $this->entityManager->remove($worldRegion);
            }

            $this->entityManager->remove($worldSector);
        }

        $this->entityManager->remove($world);
        $this->entityManager->flush();
    }

    /**
     * @param World $world
     */
    public function save(World $world): void
    {
        $this->entityManager->persist($world);
        $this->entityManager->flush();
    }
}
