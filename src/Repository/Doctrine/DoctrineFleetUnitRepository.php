<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;

final class DoctrineFleetUnitRepository implements FleetUnitRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return array<int, int>
     */
    public function getAmountSumByPlayer(Player $player): array
    {
        $results = $this->entityManager
            ->createQuery(
                'SELECT fu.gameUnit, sum(fu.amount) as total
              FROM ' . FleetUnit::class . ' fu
              JOIN ' . Fleet::class . ' f ON fu.fleet = f
              WHERE f.player = :player
              GROUP BY fu.gameUnit'
            )->setParameter('player', $player)
            ->getArrayResult();

        $gameUnits = [];
        /** @var array{gameUnit: GameUnitEnum, total: int|string} $result */
        foreach ($results as $result) {
            $gameUnits[$result['gameUnit']->value] = (int) $result['total'];
        }

        return $gameUnits;
    }

    public function remove(FleetUnit $fleetUnit): void
    {
        $this->entityManager->remove($fleetUnit);
        $this->entityManager->flush();
    }

    public function save(FleetUnit $fleetUnit): void
    {
        $this->entityManager->persist($fleetUnit);
        $this->entityManager->flush();
    }
}
