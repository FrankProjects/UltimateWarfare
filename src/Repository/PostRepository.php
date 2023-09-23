<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\User;

interface PostRepository
{
    public function find(int $id): ?Post;

    public function getLastPostByUser(User $user): ?Post;

    public function remove(Post $post): void;

    public function save(Post $post): void;
}
