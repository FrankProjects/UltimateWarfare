<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Util\ForumHelper;
use RuntimeException;

final class PostActionService
{
    private PostRepository $postRepository;
    private ForumHelper $forumHelper;

    public function __construct(
        PostRepository $postRepository,
        ForumHelper $forumHelper
    ) {
        $this->postRepository = $postRepository;
        $this->forumHelper = $forumHelper;
    }

    public function create(Post $post, Topic $topic, User $user, string $ipAddress): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);

        $post->setTopic($topic);
        $post->setPosterIp($ipAddress);
        $post->setCreateDateTime($this->forumHelper->getCurrentDateTime());
        $post->setUser($user);

        $this->postRepository->save($post);
    }

    public function edit(Post $post, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);
        $this->ensurePostPermissions($user, $post);

        $post->setEditUser($user);
        $this->postRepository->save($post);
    }

    public function remove(Post $post, ?User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);

        if ($user === null) {
            throw new RuntimeException('You are not logged in!');
        }

        $this->ensurePostPermissions($user, $post);
        $this->postRepository->remove($post);
    }

    private function ensurePostPermissions(User $user, Post $post): void
    {
        if ($user->getId() != $post->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RuntimeException('Not enough permissions!');
        }
    }
}
