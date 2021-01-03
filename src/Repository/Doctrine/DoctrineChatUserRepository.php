<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\ChatUser;
use FrankProjects\UltimateWarfare\Repository\ChatUserRepository;

final class DoctrineChatUserRepository implements ChatUserRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(ChatUser::class);
    }

    public function findByName(string $name): ?ChatUser
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    /**
     * @param int $limit
     * @return ChatUser[]
     */
    public function findWithLimit(int $limit): array
    {
        return $this->repository->findBy(
            [],
            ['name' => 'ASC'],
            $limit
        );
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

    public function remove(ChatUser $chatUser): void
    {
        $this->entityManager->remove($chatUser);
        $this->entityManager->flush();
    }

    public function save(ChatUser $chatUser): void
    {
        $this->entityManager->persist($chatUser);
        $this->entityManager->flush();
    }
}
