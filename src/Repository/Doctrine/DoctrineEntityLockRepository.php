<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;

final class DoctrineEntityLockRepository implements EntityLockRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Unlike EntityManager::refresh(), which silently does nothing for a missing row, this detects
     * a row that was deleted by a concurrent request.
     */
    public function lockForUpdate(object $entity): bool
    {
        $lockedEntity = $this->entityManager
            ->createQuery('SELECT e FROM ' . $entity::class . ' e WHERE e = :entity')
            ->setParameter('entity', $entity)
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $lockedEntity !== null;
    }
}
