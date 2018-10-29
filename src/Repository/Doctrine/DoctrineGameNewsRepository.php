<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameNews;
use FrankProjects\UltimateWarfare\Repository\GameNewsRepository;

final class DoctrineGameNewsRepository implements GameNewsRepository
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
     * DoctrineGameNewsRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(GameNews::class);
    }

    /**
     * @return GameNews[]
     */
    public function findActiveMainPageNews(): array
    {
        return $this->entityManager->createQuery(
                'SELECT gn.title, gn.message, gn.createDateTime FROM Game:GameNews gn WHERE gn.mainpage = 1 AND gn.enabled = 1 ORDER BY gn.createDateTime DESC'
            )
            ->getResult();
    }

    /**
     * @param GameNews $gameNews
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(GameNews $gameNews): void
    {
        $this->entityManager->remove($gameNews);
        $this->entityManager->flush();
    }

    /**
     * @param GameNews $gameNews
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(GameNews $gameNews): void
    {
        $this->entityManager->persist($gameNews);
        $this->entityManager->flush();
    }
}
