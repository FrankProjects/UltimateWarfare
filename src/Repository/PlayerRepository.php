<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\World;

class PlayerRepository extends EntityRepository
{
    /**
     * @param World $world
     * @param integer $limit
     * @return array
     */
    public function findByWorldAndRegions(World $world, $limit = 10)
    {
        return $this->getEntityManager()
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
     * @return array
     */
    public function findByWorldAndNetworth(World $world, $limit = 10)
    {
        return $this->getEntityManager()
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
