<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
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
     * TopicActionService service
     *
     * @param TopicRepository $topicRepository
     * @param PostRepository $postRepository
     */
    public function __construct(
        TopicRepository $topicRepository,
        PostRepository $postRepository
    ) {
        $this->topicRepository = $topicRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @param Topic $topic
     * @param Category $category
     * @param User $user
     * @param string $ipAddress
     */
    public function create(Topic $topic, Category $category, User $user, string $ipAddress): void
    {
        $this->ensureNotBanned($user);

        try {
            $dateTime = new \DateTime();
        } catch (\Exception $e) {
            throw new RunTimeException("DateTime exception: {$e->getMessage()}");
        }

        $this->ensureNoMassPost($user);

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
        $this->ensureNotBanned($user);

        if ($user->getId() != $topic->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        $this->ensureNoMassPost($user);

        $topic->setEditUser($user);
        $this->topicRepository->save($topic);
    }

    /**
     * @param Topic $topic
     * @param User $user
     */
    public function remove(Topic $topic, User $user): void
    {
        $this->ensureNotBanned($user);

        if ($user->getId() != $topic->getUser()->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }

        foreach ($topic->getPosts() as $post) {
            $this->postRepository->remove($post);
        }

        $this->topicRepository->remove($topic);
    }

    /**
     * @param User $user
     */
    private function ensureNoMassPost(User $user): void
    {
        $lastTopic = $this->topicRepository->getLastTopicByUser($user);
        try {
            $dateTime = new \DateTime('- 10 seconds');
        } catch (\Exception $e) {
            throw new RunTimeException("Spam protection exception: {$e->getMessage()}");
        }

        if ($lastTopic !== null && $lastTopic->getCreateDateTime() > $dateTime) {
            throw new RunTimeException('You can not mass post within 10 seconds!(Spam protection)');
        }
    }

    /**
     * @param User $user
     */
    private function ensureNotBanned(User $user): void
    {
        if ($user->getForumBan()) {
            throw new RunTimeException('You are forum banned!');
        }
    }
}
