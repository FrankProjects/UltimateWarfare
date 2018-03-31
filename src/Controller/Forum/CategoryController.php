<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Topic;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseForumController
{
    /**
     * @param Request $request
     * @param int $categoryId
     * @return Response
     */
    public function category(Request $request, int $categoryId): Response
    {
        $em = $this->getEm();
        $category = $em->getRepository(Category::class)
            ->find($categoryId);

        $topics = $em->getRepository(Topic::class)
            ->getByCategorySortedByStickyAndDate($category);

        return $this->render('forum/category.html.twig', [
            'category' => $category,
            'topics' => $topics,
            'user' => $this->getUser()
        ]);
    }
}
