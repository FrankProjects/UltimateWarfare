<?php

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\MarketItemType;
use FrankProjects\UltimateWarfare\Entity\World;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class MarketItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MarketItem::class);
    }

    /**
     * @param World $world
     * @param MarketItemType $marketItemType
     * @return array
     */
    public function findByWorldMarketItemType(World $world, MarketItemType $marketItemType): array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT m
              FROM Game:MarketItem m
              WHERE m.world = :world AND m.marketItemType = :marketItemType
              ORDER BY m.id DESC'
            )->setParameter('world', $world
            )->setParameter('marketItemType', $marketItemType)
            ->getResult();
    }
}
