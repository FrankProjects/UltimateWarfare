<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;

class ReportRepository extends EntityRepository
{
    /**
     * @param Player $player
     * @param integer $type
     * @param integer $limit
     * @return array
     */
    public function findReportsByType(Player $player, $type, $limit = 100)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT r
              FROM Game:Report r
              WHERE r.player = :player AND r.type = :type AND r.timestamp < :timestamp
              ORDER BY r.timestamp DESC'
            )->setParameter('timestamp', time()
            )->setParameter('player', $player
            )->setParameter('type', $type
            )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param Player $player
     * @param integer $limit
     * @return array
     */
    public function findReports(Player $player, $limit = 100)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT r
              FROM Game:Report r
              WHERE r.player = :player AND r.timestamp < :timestamp
              ORDER BY r.timestamp DESC'
            )->setParameter('timestamp', time()
            )->setParameter('player', $player
            )->setMaxResults($limit)
            ->getResult();
    }
}
