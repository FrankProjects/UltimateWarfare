<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;

final class DoctrineWorldRegionLeveledUnitRepository implements WorldRegionLeveledUnitRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<WorldRegionLeveledUnit>
     */
    private EntityRepository $repository;

    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(EntityManagerInterface $entityManager, GameUnitRegistry $gameUnitRegistry)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(WorldRegionLeveledUnit::class);
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    public function find(int $id): ?WorldRegionLeveledUnit
    {
        return $this->repository->find($id);
    }

    /**
     * @param Player $player
     * @return array<int, array<string, int>>
     */
    public function findLevelAndNetWorthByPlayer(Player $player): array
    {
        $results = $this->entityManager->createQuery(
            'SELECT wrlu.gameUnit, wrlu.level
              FROM ' . WorldRegionLeveledUnit::class . ' wrlu
              JOIN ' . WorldRegion::class . ' wr ON wrlu.worldRegion = wr
              WHERE wr.player = :player'
        )->setParameter(
            'player',
            $player
        )->getArrayResult();

        $data = [];
        /** @var array{gameUnit: GameUnitEnum, level: int} $result */
        foreach ($results as $result) {
            $gameUnit = $this->gameUnitRegistry->find($result['gameUnit']);
            $data[] = [
                'level' => $result['level'],
                'netWorth' => $gameUnit->getNetWorth(),
            ];
        }

        return $data;
    }

    public function remove(WorldRegionLeveledUnit $worldRegionLeveledUnit): void
    {
        // Keep an already loaded collection in sync; an unloaded one is fetched fresh from the database when accessed
        $worldRegion = $worldRegionLeveledUnit->getWorldRegion();
        $collection = $worldRegion->getWorldRegionLeveledUnits();
        if (!$collection instanceof AbstractLazyCollection || $collection->isInitialized()) {
            $worldRegion->removeWorldRegionLeveledUnit($worldRegionLeveledUnit);
        }

        $this->entityManager->remove($worldRegionLeveledUnit);
        $this->entityManager->flush();
    }

    public function save(WorldRegionLeveledUnit $worldRegionLeveledUnit): void
    {
        $this->entityManager->persist($worldRegionLeveledUnit);
        $this->entityManager->flush();
    }
}
