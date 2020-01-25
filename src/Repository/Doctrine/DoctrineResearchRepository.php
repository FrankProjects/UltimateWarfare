<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Repository\ResearchRepository;

final class DoctrineResearchRepository implements ResearchRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Research::class);
    }

    public function find(int $id): ?Research
    {
        return $this->repository->find($id);
    }

    /**
     * @return Research[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param Player $player
     * @return Research[]
     */
    public function findOngoingByPlayer(Player $player): array
    {
        return $this->entityManager->createQuery(
                'SELECT rp
              FROM Game:ResearchPlayer rp
              JOIN Game:Research r WITH rp.research = r
              WHERE rp.player = :player AND rp.active = 0'
            )->setParameter('player', $player
            )->getResult();
    }

    /**
     * @param Player $player
     * @return Research[]
     */
    public function findFinishedByPlayer(Player $player): array
    {
        return $this->entityManager->createQuery(
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
     * @return Research[]
     */
    public function findUnresearchedByPlayer(Player $player): array
    {
        return $this->entityManager->createQuery(
                'SELECT r
              FROM Game:Research r
              WHERE r.active = 1 AND r.id NOT IN (SELECT rp.id FROM Game:ResearchPlayer rp WHERE rp.player = :player)'
            )->setParameter('player', $player
            )->getResult();
    }

    public function remove(Research $research): void
    {
        $this->entityManager->remove($research);
        $this->entityManager->flush();
    }

    public function save(Research $research): void
    {
        $this->entityManager->persist($research);
        $this->entityManager->flush();
    }
}
