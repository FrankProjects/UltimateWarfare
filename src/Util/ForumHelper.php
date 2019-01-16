<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use RuntimeException;

final class ForumHelper
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var TopicRepository
     */
    private $topicRepository;

    /**
     * ForumHelper constructor.
     *
     * @param PostRepository $postRepository
     * @param TopicRepository $topicRepository
     */
    public function __construct(
        PostRepository $postRepository,
        TopicRepository $topicRepository
    ) {
        $this->postRepository = $postRepository;
        $this->topicRepository = $topicRepository;

    }

    /**
     * @param User $user
     */
    public function ensureNoMassPost(User $user): void
    {
        try {
            $dateTime = new \DateTime('- 10 seconds');
        } catch (\Exception $e) {
            throw new RunTimeException("Spam protection exception: {$e->getMessage()}");
        }

        $lastTopic = $this->topicRepository->getLastTopicByUser($user);
        $lastPost = $this->postRepository->getLastPostByUser($user);

        if (
            $lastTopic !== null && $lastTopic->getCreateDateTime() > $dateTime ||
            $lastPost !== null && $lastPost->getCreateDateTime() > $dateTime
        ) {
            throw new RunTimeException('You can not mass post within 10 seconds!(Spam protection)');
        }
    }

    /**
     * @param User $user
     */
    public function ensureNotBanned(User $user): void
    {
        if ($user->getForumBan()) {
            throw new RunTimeException('You are forum banned!');
        }
    }
}
