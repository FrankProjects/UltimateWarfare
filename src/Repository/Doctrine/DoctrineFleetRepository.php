<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;

final class DoctrineFleetRepository implements FleetRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Fleet>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Fleet::class);
    }

    public function findByIdAndPlayer(int $id, Player $player): ?Fleet
    {
        return $this->repository->findOneBy(['id' => $id, 'player' => $player]);
    }

    /**
     * @param Player $player
     * @return Fleet[]
     */
    public function findByPlayer(Player $player): array
    {
        return $this->repository->findBy(['player' => $player]);
    }

    /**
     * @return Fleet[]
     */
    public function findByWorldExcludingPlayer(World $world, Player $player): array
    {
        /** @var Fleet[] */
        return $this->entityManager->createQuery(
            'SELECT f, p
              FROM ' . Fleet::class . ' f
              JOIN f.player p
              WHERE p.world = :world
              AND p != :player'
        )->setParameter('world', $world)
            ->setParameter('player', $player)
            ->getResult();
    }

    public function remove(Fleet $fleet): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->entityManager->remove($fleetUnit);
        }

        $this->entityManager->remove($fleet);
        $this->entityManager->flush();
    }

    public function save(Fleet $fleet): void
    {
        $this->entityManager->persist($fleet);
        $this->entityManager->flush();
    }
}
