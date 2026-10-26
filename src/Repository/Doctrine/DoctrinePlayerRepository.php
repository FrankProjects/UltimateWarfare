<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;

final class DoctrinePlayerRepository implements PlayerRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Player>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Player::class);
    }

    public function find(int $id): ?Player
    {
        return $this->repository->find($id);
    }

    public function findByNameAndWorld(string $playerName, World $world): ?Player
    {
        return $this->repository->findOneBy(['name' => $playerName, 'world' => $world]);
    }

    /**
     * @param World $world
     * @param integer $limit
     * @return Player[]
     */
    public function findByWorldAndRegions(World $world, $limit = 10): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT p
              FROM ' . Player::class . ' p
              JOIN ' . WorldRegion::class . ' wr ON wr.player = p
              GROUP BY p.id
              HAVING p.world = :world
              ORDER BY COUNT(wr.player) DESC'
            )->setParameter(
                'world',
                $world
            )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param World $world
     * @param integer $limit
     * @return Player[]
     */
    public function findByWorldAndNetWorth(World $world, $limit = 10): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT p
              FROM ' . Player::class . ' p
              WHERE p.world = :world
              ORDER BY p.netWorth DESC'
            )->setParameter(
                'world',
                $world
            )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findWithPopulationBelowCapacityUpdatedBefore(int $timestamp, int $populationPerHouse): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT DISTINCT p
              FROM ' . Player::class . ' p
              JOIN ' . WorldRegion::class . ' wr WITH wr.player = p
              JOIN ' . WorldRegionStackableUnit::class . ' wrsu WITH wrsu.worldRegion = wr
              WHERE p.timestampUpdate < :timestamp
                AND wrsu.gameUnit = :house
                AND wr.population < wrsu.amount * :populationPerHouse'
            )->setParameter('timestamp', $timestamp)
            ->setParameter('house', GameUnitEnum::HOUSE)
            ->setParameter('populationPerHouse', $populationPerHouse)
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findWithoutFoodUpdatedBefore(int $timestamp): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT DISTINCT p
              FROM ' . Player::class . ' p
              JOIN ' . WorldRegion::class . ' wr WITH wr.player = p
              WHERE p.timestampUpdate < :timestamp
                AND wr.population > 0
                AND p.income.food < p.upkeep.food
                AND p.resources.food + (p.income.food - p.upkeep.food) * (:timestamp - p.timestampUpdate) / 3600 <= 0'
            )->setParameter('timestamp', $timestamp)
            ->getResult();
    }

    public function remove(Player $player): void
    {
        foreach ($player->getMarketItems() as $marketItem) {
            $this->entityManager->remove($marketItem);
        }

        $federation = $player->getFederation();
        if ($federation !== null) {
            $federation->setNetWorth($federation->getNetWorth() - $player->getNetWorth());
            $federation->setRegions($federation->getRegions() - count($player->getWorldRegions()));
            $this->entityManager->persist($federation);
        }


        foreach ($player->getConstructions() as $construction) {
            $this->entityManager->remove($construction);
        }

        foreach ($player->getReports() as $report) {
            $this->entityManager->remove($report);
        }

        foreach ($player->getFederationApplications() as $federationApplication) {
            $this->entityManager->remove($federationApplication);
        }

        foreach ($player->getFleets() as $fleet) {
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $this->entityManager->remove($fleetUnit);
            }
            $this->entityManager->remove($fleet);
        }

        foreach ($player->getPlayerResearch() as $playerResearch) {
            $this->entityManager->remove($playerResearch);
        }

        foreach ($player->getWorldRegions() as $worldRegion) {
            foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                $this->entityManager->remove($worldRegionStackableUnit);
            }
            foreach ($worldRegion->getWorldRegionLeveledUnits() as $worldRegionLeveledUnit) {
                $this->entityManager->remove($worldRegionLeveledUnit);
            }

            $worldRegion->setName('');
            $worldRegion->setPopulation(0);
            $worldRegion->setMorale(100);
            $worldRegion->setMoraleTimestamp(0);
            $worldRegion->setPlayer(null);
            $this->entityManager->persist($worldRegion);
        }

        foreach ($player->getFromMessages() as $message) {
            $this->entityManager->remove($message);
        }

        foreach ($player->getToMessages() as $message) {
            $this->entityManager->remove($message);
        }

        foreach ($player->getAttackerBattleSummaries() as $battleSummary) {
            $this->entityManager->remove($battleSummary);
        }

        foreach ($player->getDefenderBattleSummaries() as $battleSummary) {
            $this->entityManager->remove($battleSummary);
        }

        $this->entityManager->remove($player);
        $this->entityManager->flush();
    }

    public function save(Player $player): void
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();
    }
}
