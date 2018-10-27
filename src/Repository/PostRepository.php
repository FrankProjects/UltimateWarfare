<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\User;

interface PostRepository
{
    /**
     * @param User $user
     * @return Post|null
     */
    public function getLastPostByUser(User $user): ?Post;

    /**
     * @param Post $post
     */
    public function remove(Post $post): void;

    /**
     * @param Post $post
     */
    public function save(Post $post): void;
}
