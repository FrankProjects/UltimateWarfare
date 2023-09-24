<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use DateTime;
use Exception;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use RuntimeException;

final class ForumHelper
{
    private PostRepository $postRepository;
    private TopicRepository $topicRepository;

    public function __construct(
        PostRepository $postRepository,
        TopicRepository $topicRepository
    ) {
        $this->postRepository = $postRepository;
        $this->topicRepository = $topicRepository;
    }

    public function ensureNoMassPost(User $user): void
    {
        $dateTime = new DateTime('- 10 seconds');
        $lastTopic = $this->topicRepository->getLastTopicByUser($user);
        $lastPost = $this->postRepository->getLastPostByUser($user);

        if (
            $lastTopic !== null && $lastTopic->getCreateDateTime() > $dateTime ||
            $lastPost !== null && $lastPost->getCreateDateTime() > $dateTime
        ) {
            throw new RuntimeException('You can not mass post within 10 seconds!(Spam protection)');
        }
    }

    public function ensureNotBanned(User $user): void
    {
        if ($user->getForumBan()) {
            throw new RuntimeException('You are forum banned!');
        }
    }

    public function getCurrentDateTime(): DateTime
    {
        return new DateTime();
    }
}
