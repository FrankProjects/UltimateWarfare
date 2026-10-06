<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Exception\ForumDisabledException;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseForumController
{
    private TopicRepository $topicRepository;

    public function __construct(TopicRepository $topicRepository)
    {
        $this->topicRepository = $topicRepository;
    }

    public function category(int $categoryId): Response
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->render('forum/forum_disabled.html.twig');
        }

        $category = Category::tryFrom($categoryId);
        if ($category === null) {
            $this->addFlash('error', 'Category not found!');
            return $this->redirectToRoute('Forum');
        }

        $topics = $this->topicRepository->getByCategorySortedByStickyAndDate($category);

        return $this->render(
            'forum/category.html.twig',
            [
                'category' => $category,
                'topics' => $topics,
                'user' => $this->getGameUser()
            ]
        );
    }
}
