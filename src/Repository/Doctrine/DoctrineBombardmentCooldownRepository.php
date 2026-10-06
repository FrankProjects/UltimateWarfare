<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\BombardmentCooldown;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\BombardmentCooldownRepository;

final class DoctrineBombardmentCooldownRepository implements BombardmentCooldownRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findActiveByWorldRegionAndOperation(
        WorldRegion $worldRegion,
        string $operationSlug
    ): ?BombardmentCooldown {
        $result = $this->entityManager->createQuery(
            'SELECT bc FROM ' . BombardmentCooldown::class . ' bc
             WHERE bc.worldRegion = :region AND bc.operationSlug = :slug AND bc.cooldownUntil > :now'
        )
            ->setParameter('region', $worldRegion)
            ->setParameter('slug', $operationSlug)
            ->setParameter('now', time())
            ->setMaxResults(1)
            ->getOneOrNullResult();

        assert($result instanceof BombardmentCooldown || $result === null);

        return $result;
    }

    /**
     * @return BombardmentCooldown[]
     */
    public function findActiveByPlayer(Player $player): array
    {
        return $this->entityManager->createQuery(
            'SELECT bc FROM ' . BombardmentCooldown::class . ' bc
             JOIN bc.worldRegion wr
             WHERE wr.player = :player AND bc.cooldownUntil > :now'
        )
            ->setParameter('player', $player)
            ->setParameter('now', time())
            ->getResult();
    }

    public function save(BombardmentCooldown $bombardmentCooldown): void
    {
        $this->entityManager->persist($bombardmentCooldown);
        $this->entityManager->flush();
    }

    public function removeExpired(): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM ' . BombardmentCooldown::class . ' bc WHERE bc.cooldownUntil <= :now'
        )
            ->setParameter('now', time())
            ->execute();
    }
}
