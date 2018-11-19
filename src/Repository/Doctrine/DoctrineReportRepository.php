<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;

final class DoctrineReportRepository implements ReportRepository
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
     * DoctrineReportRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Report::class);
    }

    /**
     * @param int $id
     * @return Report|null
     */
    public function find(int $id): ?Report
    {
        return $this->repository->find($id);
    }

    /**
     * @return Report[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param Player $player
     * @param int $type
     * @param int $limit
     * @return Report[]
     */
    public function findReportsByType(Player $player, int $type, int $limit = 100): array
    {
        return $this->entityManager->createQuery(
            'SELECT r
              FROM Game:Report r
              WHERE r.player = :player AND r.type = :type AND r.timestamp < :timestamp
              ORDER BY r.timestamp DESC'
        )->setParameter('timestamp', time()
        )->setParameter('player', $player
        )->setParameter('type', $type
        )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param Player $player
     * @param int $limit
     * @return Report[]
     */
    public function findReports(Player $player, int $limit = 100): array
    {
        return $this->entityManager->createQuery(
            'SELECT r
              FROM Game:Report r
              WHERE r.player = :player AND r.timestamp < :timestamp
              ORDER BY r.timestamp DESC'
        )->setParameter('timestamp', time()
        )->setParameter('player', $player
        )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param Report $report
     */
    public function save(Report $report): void
    {
        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}
