<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;

final class DoctrineWorldRegionStackableUnitRepository implements WorldRegionStackableUnitRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<WorldRegionStackableUnit>
     */
    private EntityRepository $repository;

    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(EntityManagerInterface $entityManager, GameUnitRegistry $gameUnitRegistry)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegionStackableUnit::class);
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    public function find(int $id): ?WorldRegionStackableUnit
    {
        return $this->repository->find($id);
    }

    /**
     * @param Player $player
     * @return array<int, array<string, int>>
     */
    public function findAmountAndNetWorthByPlayer(Player $player): array
    {
        $results = $this->entityManager->createQuery(
            'SELECT wrsu.gameUnit, wrsu.amount
              FROM ' . WorldRegionStackableUnit::class . ' wrsu
              JOIN ' . WorldRegion::class . ' wr ON wrsu.worldRegion = wr
              WHERE wr.player = :player'
        )->setParameter(
            'player',
            $player
        )->getArrayResult();

        $data = [];
        /** @var array{gameUnit: GameUnitEnum, amount: int} $result */
        foreach ($results as $result) {
            $gameUnit = $this->gameUnitRegistry->find($result['gameUnit']);
            $data[] = [
                'amount' => $result['amount'],
                'netWorth' => $gameUnit->getNetWorth(),
            ];
        }

        return $data;
    }

    /**
     * @param Player $player
     * @param GameUnitCategory[] $gameUnitCategories
     * @return array<int|string, int>
     */
    public function getGameUnitSumByPlayerAndGameUnitCategories(Player $player, array $gameUnitCategories): array
    {
        $unitIds = [];
        foreach ($gameUnitCategories as $category) {
            foreach ($this->gameUnitRegistry->getIdsByCategory($category) as $id) {
                $unitIds[] = $id;
            }
        }

        if ($unitIds === []) {
            return [];
        }

        $results = $this->entityManager
            ->createQuery(
                'SELECT wrsu.gameUnit, sum(wrsu.amount) as total
              FROM ' . WorldRegionStackableUnit::class . ' wrsu
              JOIN ' . WorldRegion::class . ' wr ON wrsu.worldRegion = wr
              WHERE wr.player = :player AND wrsu.gameUnit IN (:unitIds)
              GROUP BY wrsu.gameUnit'
            )->setParameter('player', $player)
            ->setParameter('unitIds', $unitIds)
            ->getArrayResult();

        $gameUnits = [];
        /** @var array{gameUnit: GameUnitEnum, total: int} $result */
        foreach ($results as $result) {
            $gameUnits[$result['gameUnit']->value] = $result['total'];
        }

        return $gameUnits;
    }

    public function remove(WorldRegionStackableUnit $worldRegionStackableUnit): void
    {
        // Keep an already loaded collection in sync; an unloaded one is fetched fresh from the database when accessed
        $worldRegion = $worldRegionStackableUnit->getWorldRegion();
        $collection = $worldRegion->getWorldRegionStackableUnits();
        if (!$collection instanceof AbstractLazyCollection || $collection->isInitialized()) {
            $worldRegion->removeWorldRegionStackableUnit($worldRegionStackableUnit);
        }

        $this->entityManager->remove($worldRegionStackableUnit);
        $this->entityManager->flush();
    }

    public function save(WorldRegionStackableUnit $worldRegionStackableUnit): void
    {
        $this->entityManager->persist($worldRegionStackableUnit);
        $this->entityManager->flush();
    }
}
