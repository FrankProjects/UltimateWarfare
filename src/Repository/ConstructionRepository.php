<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;

final class ConstructionRepository implements ConstructionRepositoryInterface
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
     * FleetRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Construction::class);
    }

    /**
     * @param int $id
     * @return Construction|null
     */
    public function find(int $id): ?Construction
    {
        return $this->repository->find($id);
    }

    /**
     * @param Player $player
     * @return Construction[]
     */
    public function findByPlayer(Player $player): array
    {
        return $this->repository->findBy(['player' => $player]);
    }

    /**
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return Construction[]
     */
    public function findByPlayerAndGameUnitType(Player $player, GameUnitType $gameUnitType): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT c
              FROM Game:Construction c
              JOIN Game:GameUnit gu WITH c.gameUnit = gu
              WHERE c.player = :player AND gu.gameUnitType = :gameUnitType
              ORDER BY c.timestamp DESC'
            )->setParameter('player', $player
            )->setParameter('gameUnitType', $gameUnitType)
            ->getResult();
    }

    /**
     * @param int $timestamp
     * @return Construction[]
     */
    public function getCompletedConstructions(int $timestamp): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT c
              FROM Game:Construction c
              JOIN Game:GameUnit gu WITH c.gameUnit = gu
              WHERE (c.timestamp + gu.timestamp) < :timestamp'
            )->setParameter('timestamp', $timestamp)
            ->getResult();
    }

    /**
     * @param Construction $construction
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Construction $construction): void
    {
        $this->entityManager->remove($construction);
        $this->entityManager->flush();
    }

    /**
     * @param Construction $construction
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Construction $construction): void
    {
        $this->entityManager->persist($construction);
        $this->entityManager->flush();
    }
}
