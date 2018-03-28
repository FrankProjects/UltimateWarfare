<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForumController extends BaseForumController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $em = $this->getEm();
        $categories = $em->getRepository(Category::class)
            ->findAll();

        return $this->render('forum/forum.html.twig', [
            'categories' => $categories,
            'gameAccount' => $this->getGameAccount()
        ]);
    }
}
