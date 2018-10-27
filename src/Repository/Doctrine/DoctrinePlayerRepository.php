<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;

final class DoctrinePlayerRepository implements PlayerRepository
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
     * DoctrinePlayerRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Player::class);
    }

    /**
     * @param int $id
     * @return Player|null
     */
    public function find(int $id): ?Player
    {
        return $this->repository->find($id);
    }

    /**
     * @param Player $player
     */
    public function save(Player $player): void
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();
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
              FROM Game:Player p
              WHERE p.world = :world
              ORDER BY p.regions DESC'
            )->setParameter('world', $world
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
              FROM Game:Player p
              WHERE p.world = :world
              ORDER BY p.networth DESC'
            )->setParameter('world', $world
            )->setMaxResults($limit)
            ->getResult();
    }
}
