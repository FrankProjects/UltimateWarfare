<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameNews;
use FrankProjects\UltimateWarfare\Repository\GameNewsRepository;

final class DoctrineGameNewsRepository implements GameNewsRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <GameNews>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameNews::class);
    }

    public function find(int $id): ?GameNews
    {
        return $this->repository->find($id);
    }

    /**
     * @return GameNews[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @return GameNews[]
     */
    public function findActiveMainPageNews(): array
    {
        return $this->entityManager->createQuery(
            'SELECT gn.title, gn.message, gn.createDateTime FROM ' . GameNews::class . ' gn WHERE gn.mainpage = 1 AND gn.enabled = 1 ORDER BY gn.createDateTime DESC'
        )
            ->getResult();
    }

    public function remove(GameNews $gameNews): void
    {
        $this->entityManager->remove($gameNews);
        $this->entityManager->flush();
    }

    public function save(GameNews $gameNews): void
    {
        $this->entityManager->persist($gameNews);
        $this->entityManager->flush();
    }
}
