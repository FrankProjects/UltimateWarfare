<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
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
     * @param Topic $topic
     * @param User $user
     * @param string $ipAddress
     */
    public function create(Post $post, Topic $topic, User $user, string $ipAddress): void
    {
        try {
            $dateTime = new \DateTime();
        } catch (\Exception $e) {
            throw new RunTimeException("DateTime exception: {$e->getMessage()}");
        }

        $this->ensureNoMassPost($user);

        $post->setTopic($topic);
        $post->setPosterIp($ipAddress);
        $post->setCreateDateTime($dateTime);
        $post->setUser($user);

        $this->postRepository->save($post);
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

        $this->ensureNoMassPost($user);

        $post->setEditUser($user);
        $this->postRepository->save($post);
    }

    /**
     * @param Post $post
     * @param User|null $user
     */
    public function remove(Post $post, ?User $user): void
    {
        if ($user === null) {
            throw new RunTimeException('You are not logged in!');
        }

        if ($user->getId() != $post->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        $this->postRepository->remove($post);
    }

    /**
     * @param User $user
     */
    private function ensureNoMassPost(User $user): void
    {
        $lastPost = $this->postRepository->getLastPostByUser($user);
        try {
            $dateTime = new \DateTime('- 10 seconds');
        } catch (\Exception $e) {
            throw new RunTimeException("Spam protection exception: {$e->getMessage()}");
        }

        if ($lastPost !== null && $lastPost->getCreateDateTime() > $dateTime) {
            throw new RunTimeException('You can not mass post within 10 seconds!(Spam protection)');
        }
    }
}
