<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Repository\CategoryRepository;

final class DoctrineCategoryRepository implements CategoryRepository
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
     * DoctrineCategoryRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Category::class);
    }

    /**
     * @param int $id
     * @return Category|null
     */
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

    /**
     * @param Category $category
     */
    public function remove(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * @param Category $category
     */
    public function save(Category $category): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
}
