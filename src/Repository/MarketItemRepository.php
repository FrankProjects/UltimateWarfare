<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\World;

final class MarketItemRepository implements MarketItemRepositoryInterface
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
     * MarketItemRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(MarketItem::class);
    }

    /**
     * @param int $id
     * @return MarketItem|null
     */
    public function find(int $id): ?MarketItem
    {
        return $this->repository->find($id);
    }

    /**
     * @return MarketItem[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param World $world
     * @param string $type
     * @return array
     */
    public function findByWorldMarketItemType(World $world, string $type): array
    {
        return $this->entityManager->createQuery(
                'SELECT m
              FROM Game:MarketItem m
              WHERE m.world = :world AND m.type = :type
              ORDER BY m.id DESC'
            )->setParameter('world', $world
            )->setParameter('type', $type)
            ->getResult();
    }

    /**
     * @param MarketItem $marketItem
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(MarketItem $marketItem): void
    {
        $this->entityManager->remove($marketItem);
        $this->entityManager->flush();
    }

    /**
     * @param MarketItem $marketItem
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(MarketItem $marketItem): void
    {
        $this->entityManager->persist($marketItem);
        $this->entityManager->flush();
    }
}
