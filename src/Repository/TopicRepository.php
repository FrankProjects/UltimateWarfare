<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Topic;

interface TopicRepository
{
    /**
     * @param int $id
     * @return Topic|null
     */
    public function find(int $id): ?Topic;

    /**
     * @param int $limit
     * @return Topic[]
     */
    public function findLastAnnouncements(int $limit): array;

    /**
     * @param Category $category
     * @return Topic[]
     */
    public function getByCategorySortedByStickyAndDate(Category $category): array;

    /**
     * @param Topic $topic
     */
    public function remove(Topic $topic): void;

    /**
     * @param Topic $topic
     */
    public function save(Topic $topic): void;
}
