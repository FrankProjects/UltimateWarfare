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
    /**
     * @var TopicRepository
     */
    private $topicRepository;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var ForumHelper
     */
    private $forumHelper;

    /**
     * TopicActionService service
     *
     * @param TopicRepository $topicRepository
     * @param PostRepository $postRepository
     * @param ForumHelper $forumHelper
     */
    public function __construct(
        TopicRepository $topicRepository,
        PostRepository $postRepository,
        ForumHelper $forumHelper
    ) {
        $this->topicRepository = $topicRepository;
        $this->postRepository = $postRepository;
        $this->forumHelper = $forumHelper;
    }

    /**
     * @param Topic $topic
     * @param Category $category
     * @param User $user
     * @param string $ipAddress
     */
    public function create(Topic $topic, Category $category, User $user, string $ipAddress): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);

        try {
            $dateTime = new \DateTime();
        } catch (\Exception $e) {
            throw new RunTimeException("DateTime exception: {$e->getMessage()}");
        }

        $topic->setCategory($category);
        $topic->setPosterIp($ipAddress);
        $topic->setCreateDateTime($dateTime);
        $topic->setUser($user);

        $this->topicRepository->save($topic);
    }

    /**
     * @param Topic $topic
     * @param User $user
     */
    public function edit(Topic $topic, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);

        if ($user->getId() != $topic->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        $topic->setEditUser($user);
        $this->topicRepository->save($topic);
    }

    /**
     * @param Topic $topic
     * @param User $user
     */
    public function remove(Topic $topic, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);

        if ($user->getId() != $topic->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        foreach ($topic->getPosts() as $post) {
            $this->postRepository->remove($post);
        }

        $this->topicRepository->remove($topic);
    }
}
