<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\ChatLine;
use FrankProjects\UltimateWarfare\Repository\ChatLineRepository;

final class DoctrineChatLineRepository implements ChatLineRepository
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
     * DoctrineChatLineRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(ChatLine::class);
    }

    /**
     * @param int $chatLineId
     * @return ChatLine[]
     */
    public function findChatLinesByLastChatLineId(int $chatLineId): array
    {
        return $this->entityManager->createQuery(
                'SELECT cl
              FROM Game:ChatLine cl
              WHERE cl.id > :chatLineId
              ORDER BY cl.timestamp ASC'
            )->setParameter('chatLineId', $chatLineId)
            ->getResult();
    }

    /**
     * @param int $seconds
     * @return ChatLine[]
     */
    public function findChatLinesOlderThanSeconds(int $seconds): array
    {
        return $this->entityManager->createQuery(
                'SELECT cl
              FROM Game:ChatLine cl
              WHERE cl.timestamp < :timestamp'
            )->setParameter('timestamp', time() - $seconds)
            ->getResult();
    }

    /**
     * @param ChatLine $chatLine
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(ChatLine $chatLine): void
    {
        $this->entityManager->remove($chatLine);
        $this->entityManager->flush();
    }

    /**
     * @param ChatLine $chatLine
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(ChatLine $chatLine): void
    {
        $this->entityManager->persist($chatLine);
        $this->entityManager->flush();
    }
}
