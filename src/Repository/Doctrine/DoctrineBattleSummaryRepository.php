<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\BattleSummary;
use FrankProjects\UltimateWarfare\Repository\BattleSummaryRepository;

final class DoctrineBattleSummaryRepository implements BattleSummaryRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <BattleSummary>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(BattleSummary::class);
    }

    public function find(int $id): ?BattleSummary
    {
        return $this->repository->find($id);
    }

    public function save(BattleSummary $battleSummary): void
    {
        $this->entityManager->persist($battleSummary);
        $this->entityManager->flush();
    }
}
