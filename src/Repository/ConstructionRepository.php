<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;

class ConstructionRepository extends EntityRepository
{
    /**
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return array
     */
    public function findByGameUnitType(Player $player, GameUnitType $gameUnitType)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c
              FROM Game:Construction c
              JOIN Game:GameUnit gu WITH c.gameUnit = gu
              WHERE c.player = :player AND gu.gameUnitType = :gameUnitType
              ORDER BY c.timestamp DESC'
            )->setParameter('player', $player
            )->setParameter('gameUnitType', $gameUnitType)
            ->getResult();
    }

    /**
     * @return array
     */
    public function getCompletedConstructions(int $timestamp)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c
              FROM Game:Construction c
              JOIN Game:GameUnit gu WITH c.gameUnit = gu
              WHERE (c.timestamp + gu.timestamp) < :timestamp'
            )->setParameter('timestamp', $timestamp)
            ->getResult();
    }
}
