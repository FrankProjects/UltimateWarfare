<?php

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameNews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GameNewsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GameNews::class);
    }

    /**
     * @return array
     */
    public function findActiveMainPageNews()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT gn.title, gn.message, gn.createDateTime FROM Game:GameNews gn WHERE gn.mainpage = 1 AND gn.enabled = 1 ORDER BY gn.createDateTime DESC'
            )
            ->getResult();
    }
}
