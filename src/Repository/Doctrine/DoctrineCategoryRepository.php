<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Repository\CategoryRepository;

final class DoctrineCategoryRepository implements CategoryRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Category>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Category::class);
    }

    public function find(int $id): ?Category
    {
        return $this->repository->find($id);
    }

    /**
     * @return Category[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function remove(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function save(Category $category): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
}
