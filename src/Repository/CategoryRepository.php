<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Category;

interface CategoryRepository
{
    /**
     * @param int $id
     * @return Category|null
     */
    public function find(int $id): ?Category;

    /**
     * @return Category[]
     */
    public function findAll(): array;

    /**
     * @param Category $category
     */
    public function remove(Category $category): void;

    /**
     * @param Category $category
     */
    public function save(Category $category): void;
}
