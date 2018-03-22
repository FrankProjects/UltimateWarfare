<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;

class MessageRepository extends EntityRepository
{
    /**
     * @param Player $player
     * @param integer $limit
     * @return array
     */
    public function findNonDeletedMessagesToPlayer(Player $player, $limit = 100)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT m
              FROM Game:Message m
              WHERE m.toPlayer = :player AND m.toDelete = false
              ORDER BY m.timestamp DESC'
            )->setParameter('player', $player
            )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param Player $player
     * @param integer $limit
     * @return array
     */
    public function findNonDeletedMessagesFromPlayer(Player $player, $limit = 100)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT m
              FROM Game:Message m
              WHERE m.fromPlayer = :player AND m.fromDelete = false
              ORDER BY m.timestamp DESC'
            )->setParameter('player', $player
            )->setMaxResults($limit)
            ->getResult();
    }
}
