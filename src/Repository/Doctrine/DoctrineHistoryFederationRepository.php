<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\HistoryFederation;
use FrankProjects\UltimateWarfare\Entity\HistoryPlayer;
use FrankProjects\UltimateWarfare\Repository\HistoryFederationRepository;

final class DoctrineHistoryFederationRepository implements HistoryFederationRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(HistoryFederation::class);
    }

    /**
     * @param int $worldId
     * @param int $round
     * @return HistoryPlayer[]
     */
    public function findByWorldAndRound(int $worldId, int $round): array
    {
        return $this->repository->findBy(['worldId' => $worldId, 'round' => $round], ['regions' => 'DESC']);
    }

    public function save(HistoryFederation $historyFederation): void
    {
        $this->entityManager->persist($historyFederation);
        $this->entityManager->flush();
    }
}
