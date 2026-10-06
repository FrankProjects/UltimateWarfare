<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Exception\ForumDisabledException;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Response;

class ForumController extends BaseForumController
{
    public function index(TopicRepository $topicRepository): Response
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->render('forum/forum_disabled.html.twig');
        }

        $categories = Category::getAll();

        $topicCounts = [];
        $lastTopics = [];
        foreach ($categories as $category) {
            $topicCounts[$category->value] = $topicRepository->getTopicCountByCategory($category);
            $lastTopics[$category->value] = $topicRepository->getLastTopicByCategory($category);
        }

        return $this->render(
            'forum/forum.html.twig',
            [
                'categories' => $categories,
                'topicCounts' => $topicCounts,
                'lastTopics' => $lastTopics,
                'user' => $this->getGameUser()
            ]
        );
    }
}
