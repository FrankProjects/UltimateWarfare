<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;

final class DoctrineResearchPlayerRepository implements ResearchPlayerRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $timestamp
     * @return ResearchPlayer[]
     */
    public function getNonActiveCompletedResearch(int $timestamp): array
    {
        return $this->entityManager->createQuery(
            'SELECT rp
              FROM ' . ResearchPlayer::class . ' rp
              WHERE rp.active = 0 AND rp.completionTimestamp < :timestamp'
        )->setParameter(
            'timestamp',
            $timestamp
        )->getResult();
    }

    /**
     * @return ResearchPlayer[]
     */
    public function getNonActiveCompletedResearchByPlayer(Player $player, int $timestamp): array
    {
        return $this->entityManager->createQuery(
            'SELECT rp
              FROM ' . ResearchPlayer::class . ' rp
              WHERE rp.player = :player AND rp.active = 0 AND rp.completionTimestamp < :timestamp
              ORDER BY rp.completionTimestamp ASC, rp.id ASC'
        )->setParameter('player', $player)
            ->setParameter('timestamp', $timestamp)
            ->getResult();
    }

    /**
     * @param Player $player
     * @return ResearchPlayer[]
     */
    public function findOngoingByPlayer(Player $player): array
    {
        return $this->entityManager->createQuery(
            'SELECT rp
              FROM ' . ResearchPlayer::class . ' rp
              WHERE rp.player = :player AND rp.active = 0'
        )->setParameter(
            'player',
            $player
        )->getResult();
    }

    /**
     * @return array<string, int>
     */
    public function getCompletedLevelsBySlug(Player $player): array
    {
        $levels = [];
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() !== true) {
                continue;
            }

            $slug = $playerResearch->getResearchSlug();
            $level = $playerResearch->getLevel();
            if (!isset($levels[$slug]) || $level > $levels[$slug]) {
                $levels[$slug] = $level;
            }
        }

        return $levels;
    }

    public function getNextCompletionTimestampByPlayer(Player $player): ?int
    {
        /** @var int|string|null $next */
        $next = $this->entityManager->createQuery(
            'SELECT MIN(rp.completionTimestamp)
              FROM ' . ResearchPlayer::class . ' rp
              WHERE rp.player = :player AND rp.active = 0'
        )->setParameter('player', $player)
            ->getSingleScalarResult();

        return $next !== null ? (int) $next : null;
    }

    public function remove(ResearchPlayer $researchPlayer): void
    {
        $this->entityManager->remove($researchPlayer);
        $this->entityManager->flush();
    }

    public function save(ResearchPlayer $researchPlayer): void
    {
        $this->entityManager->persist($researchPlayer);
        $this->entityManager->flush();
    }
}
