<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\User;

interface TopicRepository
{
    public function find(int $id): ?Topic;

    public function getLastTopicByUser(User $user): ?Topic;

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

    public function remove(Topic $topic): void;

    public function save(Topic $topic): void;
}
