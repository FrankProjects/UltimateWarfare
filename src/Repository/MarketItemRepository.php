<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\MarketItemType;
use FrankProjects\UltimateWarfare\Entity\World;

class MarketItemRepository extends EntityRepository
{
    /**
     * @param World $world
     * @param MarketItemType $marketItemType
     * @return array
     */
    public function findByWorldMarketItemType(World $world, MarketItemType $marketItemType)
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
