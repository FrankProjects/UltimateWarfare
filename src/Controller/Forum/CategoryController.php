<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Form\Forum\CategoryType;
use FrankProjects\UltimateWarfare\Repository\CategoryRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use FrankProjects\UltimateWarfare\Service\Action\CategoryActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryController extends BaseForumController
{
    private CategoryRepository $categoryRepository;
    private TopicRepository $topicRepository;
    private CategoryActionService $categoryActionService;

    public function __construct(
        CategoryRepository $categoryRepository,
        TopicRepository $topicRepository,
        CategoryActionService $categoryActionService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->topicRepository = $topicRepository;
        $this->categoryActionService = $categoryActionService;
    }

    public function category(int $categoryId): Response
    {
        $this->ensureForumEnabled();

        $category = $this->categoryRepository->find($categoryId);
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

    public function create(Request $request): Response
    {
        $this->ensureForumEnabled();

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getGameUser() !== null) {
            try {
                $this->categoryActionService->create($category, $this->getGameUser());
                $this->addFlash('success', 'Successfully created category');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum');
        }

        return $this->render(
            'forum/category/create.html.twig',
            [
                'category' => $category,
                'user' => $this->getGameUser(),
                'form' => $form->createView()
            ]
        );
    }

    public function edit(Request $request, int $categoryId): Response
    {
        $this->ensureForumEnabled();

        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            $this->addFlash('error', 'No such category!');
            return $this->redirectToRoute('Forum');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->categoryActionService->edit($category, $this->getGameUser());
                $this->addFlash('success', 'Successfully edited category');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum');
        }

        return $this->render(
            'forum/category/edit.html.twig',
            [
                'category' => $category,
                'user' => $this->getGameUser(),
                'form' => $form->createView()
            ]
        );
    }

    public function remove(int $categoryId): RedirectResponse
    {
        $this->ensureForumEnabled();

        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            $this->addFlash('error', 'No such category!');
            return $this->redirectToRoute('Forum');
        }

        try {
            $this->categoryActionService->remove($category, $this->getGameUser());
            $this->addFlash('success', 'Category removed');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Forum');
    }
}
