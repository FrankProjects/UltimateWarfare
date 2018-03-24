<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;

class ResearchRepository extends EntityRepository
{
    /**
     * @param Player $player
     * @return array
     */
    public function findOngoingByPlayer(Player $player)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT rp
              FROM Game:ResearchPlayer rp
              JOIN Game:Research r WITH rp.research = r
              WHERE rp.player = :player AND rp.active = 0'
            )->setParameter('player', $player
            )->getResult();
    }

    /**
     * @param Player $player
     * @return array
     */
    public function findFinishedByPlayer(Player $player)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT r
              FROM Game:ResearchPlayer rp
              JOIN Game:Research r WITH rp.research = r
              WHERE rp.player = :player AND rp.active = 1
              ORDER BY rp.timestamp DESC'
            )->setParameter('player', $player
            )->getResult();
    }

    /**
     * @param Player $player
     * @return array
     */
    public function findUnresearchedByPlayer(Player $player)
    {
        //$result = $db->query("SELECT research.id, research.name, research.pic, research.cost, research.timestamp, research.description from research WHERE research.active = 1 AND research.id NOT IN (SELECT research_id FROM research_player WHERE research_id = research.id AND player_id = $player_id) AND research.id NOT IN (SELECT research_id FROM researching WHERE research_id = research.id AND player_id = $player_id)");

        return $this->getEntityManager()
            ->createQuery(
                'SELECT r
              FROM Game:Research r
              WHERE r.active = 1 AND r.id NOT IN (SELECT rp.id FROM Game:ResearchPlayer rp WHERE rp.player = :player)'
            )->setParameter('player', $player
            )->getResult();
    }
}
