<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;

final class DoctrineConstructionRepository implements ConstructionRepository
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
     * DoctrineConstructionRepository constructor.
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
     * @param WorldRegion $worldRegion
     * @return array
     */
    public function getGameUnitConstructionSumByWorldRegion(WorldRegion $worldRegion): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT gu.id, sum(c.number) as total
              FROM Game:Construction c
              JOIN Game:GameUnit gu WITH c.gameUnit = gu
              WHERE c.worldRegion = :worldRegion
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
     * @param Player $player
     * @return array
     */
    public function getGameUnitConstructionSumByPlayer(Player $player): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT gu.id, sum(c.number) as total
              FROM Game:Construction c
              JOIN Game:GameUnit gu WITH c.gameUnit = gu
              WHERE c.player = :player
              GROUP BY gu.id'
            )->setParameter('player', $player)
            ->getArrayResult();

        $gameUnits = [];
        foreach ($results as $result) {
            $gameUnits[$result['id']] = $result['total'];
        }

        return $gameUnits;
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
     */
    public function remove(Construction $construction): void
    {
        $this->entityManager->remove($construction);
        $this->entityManager->flush();
    }

    /**
     * @param Construction $construction
     */
    public function save(Construction $construction): void
    {
        $this->entityManager->persist($construction);
        $this->entityManager->flush();
    }
}
