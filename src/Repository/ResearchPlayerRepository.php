<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;

class ResearchPlayerRepository extends EntityRepository
{
    /**
     * @param int $timestamp
     * @return array
     */
    public function getNonActiveCompletedResearch(int $timestamp)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT rp
              FROM Game:ResearchPlayer rp
              JOIN Game:Research r WITH rp.research = r
              WHERE rp.active = 0 AND (rp.timestamp + r.timestamp) < :timestamp'
            )->setParameter('timestamp', $timestamp
            )->getResult();
    }
}
