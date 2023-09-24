<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use FrankProjects\UltimateWarfare\Util\ForumHelper;
use RuntimeException;

final class TopicActionService
{
    private TopicRepository $topicRepository;
    private PostRepository $postRepository;
    private ForumHelper $forumHelper;

    public function __construct(
        TopicRepository $topicRepository,
        PostRepository $postRepository,
        ForumHelper $forumHelper
    ) {
        $this->topicRepository = $topicRepository;
        $this->postRepository = $postRepository;
        $this->forumHelper = $forumHelper;
    }

    public function create(Topic $topic, Category $category, User $user, string $ipAddress): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);
        $dateTime = $this->forumHelper->getCurrentDateTime();

        $topic->setCategory($category);
        $topic->setPosterIp($ipAddress);
        $topic->setCreateDateTime($dateTime);
        $topic->setUser($user);

        $this->topicRepository->save($topic);
    }

    public function edit(Topic $topic, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);
        $this->ensureTopicPermissions($user, $topic);

        $topic->setEditUser($user);
        $this->topicRepository->save($topic);
    }

    public function remove(Topic $topic, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->ensureTopicPermissions($user, $topic);

        foreach ($topic->getPosts() as $post) {
            $this->postRepository->remove($post);
        }

        $this->topicRepository->remove($topic);
    }

    public function sticky(Topic $topic, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->ensureTopicPermissions($user, $topic);

        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new RuntimeException('Not enough permissions!');
        }

        $topic->setSticky(true);
        $this->topicRepository->save($topic);
    }

    public function unsticky(Topic $topic, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->ensureTopicPermissions($user, $topic);

        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new RuntimeException('Not enough permissions!');
        }

        $topic->setSticky(false);
        $this->topicRepository->save($topic);
    }

    private function ensureTopicPermissions(User $user, Topic $topic): void
    {
        if ($user->getId() != $topic->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RuntimeException('Not enough permissions!');
        }
    }
}
