<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
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
              JOIN ' . WorldRegion::class . ' wr WITH wr.player = p
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
    public function findByWorldAndNetworth(World $world, $limit = 10): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT p
              FROM ' . Player::class . ' p
              WHERE p.world = :world
              ORDER BY p.networth DESC'
            )->setParameter(
                'world',
                $world
            )->setMaxResults($limit)
            ->getResult();
    }

    public function remove(Player $player): void
    {
        foreach ($player->getMarketItems() as $marketItem) {
            $this->entityManager->remove($marketItem);
        }

        $federation = $player->getFederation();
        if ($federation !== null) {
            // XXX TODO: Delete Federation if you are owner
            $federation->setNetworth($federation->getNetworth() - $player->getNetworth());
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
            $this->entityManager->remove($fleet);
        }

        foreach ($player->getPlayerResearch() as $playerResearch) {
            $this->entityManager->remove($playerResearch);
        }

        foreach ($player->getWorldRegions() as $worldRegion) {
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                $this->entityManager->remove($worldRegionUnit);
            }

            $worldRegion->setName('');
            $worldRegion->setPlayer(null);
            $this->entityManager->persist($worldRegion);
        }

        // XXX TODO: Do we want to keep messages so other players don't lose messages from their in/outbox?
        foreach ($player->getFromMessages() as $message) {
            $this->entityManager->remove($message);
        }

        foreach ($player->getToMessages() as $message) {
            $this->entityManager->remove($message);
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
