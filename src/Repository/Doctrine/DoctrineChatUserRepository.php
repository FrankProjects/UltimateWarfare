<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\ChatUser;
use FrankProjects\UltimateWarfare\Repository\ChatUserRepository;

final class DoctrineChatUserRepository implements ChatUserRepository
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
     * DoctrineChatUserRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(ChatUser::class);
    }

    /**
     * @return ChatUser[]
     */
    public function findInactiveChatUsers(): array
    {
        return $this->entityManager->createQuery(
                'SELECT cu
              FROM Game:ChatUser cu
              WHERE cu.timestampActivity < :timestamp'
            )->setParameter('timestamp', time() - 25)
            ->getResult();
    }

    /**
     * @param ChatUser $chatUser
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(ChatUser $chatUser): void
    {
        $this->entityManager->remove($chatUser);
        $this->entityManager->flush();
    }

    /**
     * @param ChatUser $chatUser
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(ChatUser $chatUser): void
    {
        $this->entityManager->persist($chatUser);
        $this->entityManager->flush();
    }
}
