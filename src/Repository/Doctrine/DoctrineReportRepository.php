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
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Report>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Report::class);
    }

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
              FROM ' . Report::class . ' r
              WHERE r.player = :player AND r.type = :type AND r.timestamp < :timestamp
              ORDER BY r.timestamp DESC'
        )->setParameter(
            'timestamp',
            time()
        )->setParameter(
            'player',
            $player
        )->setParameter(
            'type',
            $type
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
              FROM ' . Report::class . ' r
              WHERE r.player = :player AND r.timestamp < :timestamp
              ORDER BY r.timestamp DESC'
        )->setParameter(
            'timestamp',
            time()
        )->setParameter(
            'player',
            $player
        )->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param Player $player
     * @param int|null $type
     * @param int $limit
     * @param int $offset
     * @return Report[]
     */
    public function findReportsPaginated(Player $player, ?int $type, int $limit = 25, int $offset = 0): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r')
            ->from(Report::class, 'r')
            ->where('r.player = :player')
            ->andWhere('r.timestamp < :timestamp')
            ->setParameter('player', $player)
            ->setParameter('timestamp', time())
            ->orderBy('r.timestamp', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($type !== null) {
            $qb->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Player $player
     * @param int|null $type
     * @return int
     */
    public function countReports(Player $player, ?int $type = null): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(r.id)')
            ->from(Report::class, 'r')
            ->where('r.player = :player')
            ->andWhere('r.timestamp < :timestamp')
            ->setParameter('player', $player)
            ->setParameter('timestamp', time());

        if ($type !== null) {
            $qb->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function remove(Report $report): void
    {
        $this->entityManager->remove($report);
        $this->entityManager->flush();
    }

    public function save(Report $report): void
    {
        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}
