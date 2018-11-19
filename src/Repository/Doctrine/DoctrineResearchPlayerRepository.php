<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;

final class DoctrineResearchPlayerRepository implements ResearchPlayerRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * DoctrineResearchPlayerRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(ResearchPlayer::class);
    }

    /**
     * @param int $timestamp
     * @return ResearchPlayer[]
     */
    public function getNonActiveCompletedResearch(int $timestamp): array
    {
        return $this->entityManager->createQuery(
                'SELECT rp
              FROM Game:ResearchPlayer rp
              JOIN Game:Research r WITH rp.research = r
              WHERE rp.active = 0 AND (rp.timestamp + r.timestamp) < :timestamp'
            )->setParameter('timestamp', $timestamp
            )->getResult();
    }

    /**
     * @param ResearchPlayer $researchPlayer
     */
    public function remove(ResearchPlayer $researchPlayer): void
    {
        $this->entityManager->remove($researchPlayer);
        $this->entityManager->flush();
    }

    /**
     * @param ResearchPlayer $researchPlayer
     */
    public function save(ResearchPlayer $researchPlayer): void
    {
        $this->entityManager->persist($researchPlayer);
        $this->entityManager->flush();
    }
}
