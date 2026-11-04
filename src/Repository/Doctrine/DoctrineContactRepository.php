<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Contact;
use FrankProjects\UltimateWarfare\Repository\ContactRepository;

final class DoctrineContactRepository implements ContactRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Contact>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Contact::class);
    }

    #[\Override]
    public function find(int $id): ?Contact
    {
        return $this->repository->find($id);
    }

    /**
     * @return Contact[]
     */
    #[\Override]
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    #[\Override]
    public function remove(Contact $contact): void
    {
        $this->entityManager->remove($contact);
        $this->entityManager->flush();
    }

    #[\Override]
    public function save(Contact $contact): void
    {
        $this->entityManager->persist($contact);
        $this->entityManager->flush();
    }

    #[\Override]
    public function count(): int
    {
        return $this->repository->count();
    }
}
