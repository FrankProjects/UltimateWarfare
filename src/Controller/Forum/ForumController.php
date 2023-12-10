<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;

class ForumController extends BaseForumController
{
    public function index(CategoryRepository $categoryRepository): Response
    {
        try {
            $this->ensureForumEnabled();
        } catch (\Exception $exception) {
            return $this->render('forum/forum_disabled.html.twig');
        }

        $categories = $categoryRepository->findAll();

        return $this->render(
            'forum/forum.html.twig',
            [
                'categories' => $categories,
                'user' => $this->getGameUser()
            ]
        );
    }
}
