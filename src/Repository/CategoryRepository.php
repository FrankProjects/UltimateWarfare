<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Category;

interface CategoryRepository
{
    public function find(int $id): ?Category;

    /**
     * @return Category[]
     */
    public function findAll(): array;

    public function remove(Category $category): void;

    public function save(Category $category): void;
}
