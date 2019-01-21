<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Contact;
use FrankProjects\UltimateWarfare\Repository\ContactRepository;

final class DoctrineContactRepository implements ContactRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * DoctrineContactRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Contact::class);
    }

    /**
     * @param int $id
     * @return Contact|null
     */
    public function find(int $id): ?Contact
    {
        return $this->repository->find($id);
    }

    /**
     * @return Contact[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param Contact $contact
     */
    public function remove(Contact $contact): void
    {
        $this->entityManager->remove($contact);
        $this->entityManager->flush();
    }

    /**
     * @param Contact $contact
     */
    public function save(Contact $contact): void
    {
        $this->entityManager->persist($contact);
        $this->entityManager->flush();
    }
}
