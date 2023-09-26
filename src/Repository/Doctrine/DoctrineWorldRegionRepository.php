<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;

final class DoctrineWorldRegionRepository implements WorldRegionRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <WorldRegion>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegion::class);
    }

    public function find(int $id): ?WorldRegion
    {
        return $this->repository->find($id);
    }

    /**
     * @param WorldSector $worldSector
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldSectorAndPlayer(WorldSector $worldSector, ?Player $player): array
    {
        return $this->repository->findBy(['worldSector' => $worldSector, 'player' => $player]);
    }

    /**
     * @param World $world
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldAndPlayer(World $world, ?Player $player): array
    {
        return $this->repository->findBy(['world' => $world, 'player' => $player]);
    }

    public function findByWorldXY(World $world, int $x, int $y): ?WorldRegion
    {
        return $this->repository->findOneBy(['world' => $world, 'x' => $x, 'y' => $y]);
    }

    /**
     * @param WorldRegion $worldRegion
     * @return array<int|string, mixed>
     */
    public function getWorldGameUnitSumByWorldRegion(WorldRegion $worldRegion): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT gu.id, sum(wru.amount) as total
              FROM ' . WorldRegionUnit::class . ' wru
              JOIN ' . GameUnit::class . ' gu WITH wru.gameUnit = gu
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

    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion
    {
        return $this->entityManager
            ->createQuery(
                'SELECT wr
              FROM ' . WorldRegion::class . ' wr
              WHERE wr.id < :id AND wr.player = :player
              ORDER BY wr.id DESC'
            )->setParameter('id', $id)
            ->setParameter('player', $player)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion
    {
        return $this->entityManager
            ->createQuery(
                'SELECT wr
              FROM ' . WorldRegion::class . ' wr
              WHERE wr.id > :id AND wr.player = :player
              ORDER BY wr.id ASC'
            )->setParameter('id', $id)
            ->setParameter('player', $player)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function save(WorldRegion $worldRegion): void
    {
        $this->entityManager->persist($worldRegion);
        $this->entityManager->flush();
    }
}
