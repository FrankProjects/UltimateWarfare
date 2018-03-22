<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;

class ChatLineRepository extends EntityRepository
{
    /**
     * @param int $chatLineId
     * @return array
     */
    public function findChatLinesByLastChatLineId(int $chatLineId)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT cl
              FROM Game:ChatLine cl
              WHERE cl.id > :chatLineId
              ORDER BY cl.timestamp ASC'
            )->setParameter('chatLineId', $chatLineId)
            ->getResult();
    }

    /**
     * @param int $seconds
     * @return array
     */
    public function findChatLinesOlderThanSeconds(int $seconds)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT cl
              FROM Game:ChatLine cl
              WHERE cl.timestamp < :timestamp'
            )->setParameter('timestamp', time() - $seconds)
            ->getResult();
    }
}
