<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;

final class DoctrineWorldRegionRepository implements WorldRegionRepository
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
     * DoctrineWorldRegionRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegion::class);
    }

    /**
     * @param int $id
     * @return WorldRegion|null
     */
    public function find(int $id): ?WorldRegion
    {
        return $this->repository->find($id);
    }

    /**
     * @param WorldRegion $worldRegion
     * @return array
     */
    public function getWorldGameUnitSumByWorldRegion(WorldRegion $worldRegion): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT gu.id, sum(wru.amount) as total
              FROM Game:WorldRegionUnit wru
              JOIN Game:GameUnit gu WITH wru.gameUnit = gu
              WHERE wru.worldRegion = :worldRegion
              GROUP BY gu.id'
            )->setParameter('worldRegion', $worldRegion)
            ->getArrayResult();

        $gameUnits = [];
        foreach ($results as $result) {
            $gameUnits[$result['id']] = $result['total'];
        }

        return $gameUnits;
    }

    /**
     * @param int $id
     * @param Player $player
     * @return WorldRegion|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion
    {
        return $this->entityManager
            ->createQuery(
                'SELECT wr
              FROM Game:WorldRegion wr
              WHERE wr.id < :id AND wr.player = :player
              ORDER BY wr.id DESC'
            )->setParameter('id', $id)
            ->setParameter('player', $player)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @param Player $player
     * @return WorldRegion|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion
    {
        return $this->entityManager
            ->createQuery(
                'SELECT wr
              FROM Game:WorldRegion wr
              WHERE wr.id > :id AND wr.player = :player
              ORDER BY wr.id ASC'
            )->setParameter('id', $id)
            ->setParameter('player', $player)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param WorldRegion $worldRegion
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(WorldRegion $worldRegion): void
    {
        $this->entityManager->persist($worldRegion);
        $this->entityManager->flush();
    }
}
