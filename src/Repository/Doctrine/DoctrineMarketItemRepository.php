<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;

final class DoctrineMarketItemRepository implements MarketItemRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <MarketItem>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(MarketItem::class);
    }

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
     * @return MarketItem[]
     */
    public function findByWorldMarketItemType(World $world, string $type): array
    {
        return $this->entityManager->createQuery(
            'SELECT m
              FROM ' . MarketItem::class . ' m
              WHERE m.world = :world AND m.type = :type
              ORDER BY m.id DESC'
        )->setParameter(
            'world',
            $world
        )->setParameter('type', $type)
            ->getResult();
    }

    public function remove(MarketItem $marketItem): void
    {
        $this->entityManager->remove($marketItem);
        $this->entityManager->flush();
    }

    public function save(MarketItem $marketItem): void
    {
        $this->entityManager->persist($marketItem);
        $this->entityManager->flush();
    }
}
