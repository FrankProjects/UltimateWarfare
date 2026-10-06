<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;

final class DoctrineConstructionRepository implements ConstructionRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Construction>
     */
    private EntityRepository $repository;

    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(EntityManagerInterface $entityManager, GameUnitRegistry $gameUnitRegistry)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Construction::class);
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

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

    public function getGameUnitConstructionSumByWorldRegionAndCategory(
        WorldRegion $worldRegion,
        GameUnitCategory $gameUnitCategory
    ): int {
        $unitIds = $this->gameUnitRegistry->getIdsByCategory($gameUnitCategory);

        if ($unitIds === []) {
            return 0;
        }

        $results = $this->entityManager
            ->createQuery(
                'SELECT sum(c.number) as total
              FROM ' . Construction::class . ' c
              WHERE c.worldRegion = :worldRegion AND c.gameUnit IN (:unitIds)'
            )->setParameter('worldRegion', $worldRegion)
            ->setParameter('unitIds', $unitIds)
            ->getArrayResult();

        /** @var array{total: numeric-string|null} $result */
        $result = $results[0] ?? ['total' => null];

        return (int) ($result['total'] ?? 0);
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function getGameUnitConstructionSumByPlayerGroupedByRegion(Player $player): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT IDENTITY(c.worldRegion) as regionId, c.gameUnit, sum(c.number) as total
              FROM ' . Construction::class . ' c
              WHERE c.player = :player
              GROUP BY regionId, c.gameUnit'
            )->setParameter('player', $player)
            ->getArrayResult();

        $grouped = [];
        /** @var array{regionId: string, gameUnit: GameUnitEnum, total: string} $result */
        foreach ($results as $result) {
            $grouped[(int) $result['regionId']][$result['gameUnit']->value] = (int) $result['total'];
        }

        return $grouped;
    }

    public function getGameUnitConstructionSumByPlayer(Player $player): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT c.gameUnit, sum(c.number) as total
              FROM ' . Construction::class . ' c
              WHERE c.player = :player
              GROUP BY c.gameUnit'
            )->setParameter('player', $player)
            ->getArrayResult();

        $gameUnits = [];
        /** @var array{gameUnit: GameUnitEnum, total: int} $result */
        foreach ($results as $result) {
            $gameUnits[$result['gameUnit']->value] = $result['total'];
        }

        return $gameUnits;
    }

    /**
     * @param Player $player
     * @param GameUnitCategory $gameUnitCategory
     * @return Construction[]
     */
    public function findByPlayerAndGameUnitCategory(Player $player, GameUnitCategory $gameUnitCategory): array
    {
        $unitIds = $this->gameUnitRegistry->getIdsByCategory($gameUnitCategory);

        if ($unitIds === []) {
            return [];
        }

        return $this->entityManager
            ->createQuery(
                'SELECT c
              FROM ' . Construction::class . ' c
              WHERE c.player = :player AND c.gameUnit IN (:unitIds)
              ORDER BY c.timestamp DESC'
            )->setParameter('player', $player)
            ->setParameter('unitIds', $unitIds)
            ->getResult();
    }

    /**
     * @param int $timestamp
     * @return Construction[]
     */
    public function getCompletedConstructions(int $timestamp): array
    {
        /** @var Construction[] $completed */
        $completed = $this->entityManager
            ->createQuery(
                'SELECT c
              FROM ' . Construction::class . ' c
              WHERE c.timestamp + c.duration < :timestamp'
            )->setParameter('timestamp', $timestamp)
            ->getResult();

        return $completed;
    }

    /**
     * @return Construction[]
     */
    public function getAllConstructions(): array
    {
        return $this->repository->findAll();
    }

    public function remove(Construction $construction): void
    {
        // Keep an already loaded collection in sync; an unloaded one is fetched fresh from the database when accessed
        $worldRegion = $construction->getWorldRegion();
        $collection = $worldRegion->getConstructions();
        if (!$collection instanceof AbstractLazyCollection || $collection->isInitialized()) {
            $worldRegion->removeConstruction($construction);
        }

        $this->entityManager->remove($construction);
        $this->entityManager->flush();
    }

    public function save(Construction $construction): void
    {
        $this->entityManager->persist($construction);
        $this->entityManager->flush();
    }
}
