<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use RuntimeException;

final class PostActionService
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * PostActionService service
     *
     * @param PostRepository $postRepository
     */
    public function __construct(
        PostRepository $postRepository
    ) {
        $this->postRepository = $postRepository;
    }

    /**
     * @param Post $post
     * @param User $user
     * @throws \Exception
     */
    public function edit(Post $post, User $user): void
    {
        if ($user->getId() != $post->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        $lastPost = $this->postRepository->getLastPostByUser($user);

        if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
            throw new RunTimeException('You can not mass post within 10 seconds!(Spam protection)');
        }

        $post->setEditUser($user);
        $this->postRepository->save($post);
    }

    /**
     * @param int $postId
     * @param User|null $user
     */
    public function remove(int $postId, ?User $user): void
    {
        $post = $this->getPostByIdAndUser($postId, $user);
        $this->postRepository->remove($post);
    }


    /**
     * @param int $postId
     * @param User|null $user
     * @return Post
     */
    private function getPostByIdAndUser(int $postId, ?User $user): Post
    {
        $post = $this->postRepository->find($postId);

        if ($post === null) {
            throw new RunTimeException('Post does not exist!');
        }

        if ($user === null) {
            throw new RunTimeException('You are not logged in!');
        }

        if ($user->getId() != $post->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        return $post;
    }
}
