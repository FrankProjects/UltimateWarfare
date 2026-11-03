<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;

final class DoctrineUnbanRequestRepository implements UnbanRequestRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <UnbanRequest>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(UnbanRequest::class);
    }

    #[\Override]
    public function find(int $id): ?UnbanRequest
    {
        return $this->repository->find($id);
    }

    /**
     * @return UnbanRequest[]
     */
    #[\Override]
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    #[\Override]
    public function findByUser(User $user): ?UnbanRequest
    {
        return $this->repository->findOneBy(['user' => $user]);
    }

    #[\Override]
    public function remove(UnbanRequest $unbanRequest): void
    {
        $this->entityManager->remove($unbanRequest);
        $this->entityManager->flush();
    }

    #[\Override]
    public function save(UnbanRequest $unbanRequest): void
    {
        $this->entityManager->persist($unbanRequest);
        $this->entityManager->flush();
    }

    #[\Override]
    public function count(): int
    {
        return $this->repository->count();
    }
}
