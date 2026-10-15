<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
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
     * @return WorldRegion[]
     */
    public function findByPlayerWithUnitsAndConstructions(Player $player): array
    {
        // Separate fetch joins per collection, as joining them all at once multiplies the result rows
        $worldRegions = [];
        foreach (['worldRegionStackableUnits', 'worldRegionLeveledUnits', 'constructions'] as $collection) {
            /** @var WorldRegion[] $worldRegions */
            $worldRegions = $this->entityManager
                ->createQuery(
                    'SELECT wr, item
                  FROM ' . WorldRegion::class . ' wr
                  LEFT JOIN wr.' . $collection . ' item
                  WHERE wr.player = :player'
                )->setParameter('player', $player)
                ->getResult();
        }

        return $worldRegions;
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function getWorldGameUnitSumByPlayer(Player $player): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT IDENTITY(wrsu.worldRegion) as regionId, wrsu.gameUnit, sum(wrsu.amount) as total
              FROM ' . WorldRegionStackableUnit::class . ' wrsu
              JOIN ' . WorldRegion::class . ' wr ON wrsu.worldRegion = wr
              WHERE wr.player = :player
              GROUP BY regionId, wrsu.gameUnit'
            )->setParameter('player', $player)
            ->getArrayResult();

        $grouped = [];
        /** @var array{regionId: string, gameUnit: GameUnitEnum, total: string} $result */
        foreach ($results as $result) {
            $grouped[(int) $result['regionId']][$result['gameUnit']->value] = (int) $result['total'];
        }

        return $grouped;
    }

    /**
     * @return list<array{WorldRegion, int}>
     */
    public function findBelowPopulationCapacityByPlayer(Player $player, int $populationPerHouse): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT wr, wrsu.amount AS houses
              FROM ' . WorldRegion::class . ' wr
              JOIN ' . WorldRegionStackableUnit::class . ' wrsu WITH wrsu.worldRegion = wr
              WHERE wr.player = :player
                AND wrsu.gameUnit = :house
                AND wr.population < wrsu.amount * :populationPerHouse'
            )->setParameter('player', $player)
            ->setParameter('house', GameUnitEnum::HOUSE)
            ->setParameter('populationPerHouse', $populationPerHouse)
            ->getResult();

        $worldRegions = [];
        foreach ($results as $result) {
            $worldRegions[] = [$result[0], $result['houses']];
        }

        return $worldRegions;
    }

    /**
     * @return WorldRegion[]
     */
    public function findWithPopulationByPlayer(Player $player): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT wr
              FROM ' . WorldRegion::class . ' wr
              WHERE wr.player = :player AND wr.population > 0'
            )->setParameter('player', $player)
            ->getResult();
    }

    public function getPopulationSumByPlayer(Player $player): int
    {
        $total = $this->entityManager
            ->createQuery(
                'SELECT SUM(wr.population)
              FROM ' . WorldRegion::class . ' wr
              WHERE wr.player = :player'
            )->setParameter('player', $player)
            ->getSingleScalarResult();

        return is_numeric($total) ? (int) $total : 0;
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
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
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
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * Find 6 hex neighbors (pointy-top, odd-r offset)
     * @return WorldRegion[]
     */
    public function findAdjacentRegions(int $x, int $y, World $world): array
    {
        // Hex neighbors differ based on even/odd row
        if ($y % 2 === 0) {
            // Even row neighbors
            $neighbors = [
                [$x - 1, $y], [$x + 1, $y],
                [$x - 1, $y - 1], [$x, $y - 1],
                [$x - 1, $y + 1], [$x, $y + 1],
            ];
        } else {
            // Odd row neighbors (shifted right)
            $neighbors = [
                [$x - 1, $y], [$x + 1, $y],
                [$x, $y - 1], [$x + 1, $y - 1],
                [$x, $y + 1], [$x + 1, $y + 1],
            ];
        }

        $conditions = [];
        $parameters = ['world' => $world];
        foreach ($neighbors as $i => [$nx, $ny]) {
            $conditions[] = "(wr.x = :x{$i} AND wr.y = :y{$i})";
            $parameters["x{$i}"] = $nx;
            $parameters["y{$i}"] = $ny;
        }

        $dql = 'SELECT wr FROM ' . WorldRegion::class . ' wr'
            . ' WHERE wr.world = :world'
            . ' AND (' . implode(' OR ', $conditions) . ')';

        $query = $this->entityManager->createQuery($dql);
        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
        }

        /** @var WorldRegion[] $result */
        $result = $query->getResult();

        return $result;
    }

    public function save(WorldRegion $worldRegion): void
    {
        $this->entityManager->persist($worldRegion);
        $this->entityManager->flush();
    }

    /**
     * @param WorldRegion[] $worldRegions
     */
    public function saveAll(array $worldRegions): void
    {
        foreach ($worldRegions as $worldRegion) {
            $this->entityManager->persist($worldRegion);
        }

        $this->entityManager->flush();
    }

    public function removeForWorld(World $world): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM ' . WorldRegion::class . ' wr WHERE wr.world = :world'
        )->setParameter('world', $world)
            ->execute();
    }
}
