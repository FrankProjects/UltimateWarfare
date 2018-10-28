<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Repository\CategoryRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseForumController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var TopicRepository
     */
    private $topicRepository;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param TopicRepository $topicRepository
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        TopicRepository $topicRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->topicRepository = $topicRepository;
    }

    /**
     * @param int $categoryId
     * @return Response
     */
    public function category(int $categoryId): Response
    {
        $category = $this->categoryRepository->find($categoryId);
        $topics = $this->topicRepository->getByCategorySortedByStickyAndDate($category);

        return $this->render('forum/category.html.twig', [
            'category' => $category,
            'topics' => $topics,
            'user' => $this->getGameUser()
        ]);
    }
}
