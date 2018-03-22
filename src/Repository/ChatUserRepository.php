<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;

class ChatUserRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findInactiveChatUsers()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT cu
              FROM Game:ChatUser cu
              WHERE cu.timestampActivity < :timestamp'
            )->setParameter('timestamp', time() - 25)
                ->getResult();
    }
}
