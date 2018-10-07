<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;

class FleetRepository implements FleetRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * FleetRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Fleet::class);
    }

    /**
     * @param int $id
     * @return Fleet|null
     */
    public function find(int $id): ?Fleet
    {
        return $this->repository->find($id);
    }

    /**
     * @param Player $player
     * @return array
     */
    public function findByPlayer(Player $player): array
    {
        return $this->repository->findBy(['player' => $player]);
    }

    /**
     * @param Fleet $fleet
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Fleet $fleet): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $this->entityManager->remove($fleetUnit);
        }

        $this->entityManager->remove($fleet);
        $this->entityManager->flush();
    }

    /**
     * @param Fleet $fleet
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Fleet $fleet): void
    {
        $this->entityManager->persist($fleet);
        $this->entityManager->flush();
    }
}
