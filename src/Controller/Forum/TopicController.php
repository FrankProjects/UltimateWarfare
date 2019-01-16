<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Form\Forum\PostType;
use FrankProjects\UltimateWarfare\Form\Forum\TopicType;
use FrankProjects\UltimateWarfare\Repository\CategoryRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use FrankProjects\UltimateWarfare\Service\Action\PostActionService;
use FrankProjects\UltimateWarfare\Service\Action\TopicActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TopicController extends BaseForumController
{
    /**
     * @var TopicActionService
     */
    private $topicActionService;

    /**
     * @var PostActionService
     */
    private $postActionService;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var TopicRepository
     */
    private $topicRepository;

    /**
     * TopicController constructor.
     *
     * @param TopicActionService $topicActionService
     * @param PostActionService $postActionService
     * @param CategoryRepository $categoryRepository
     * @param TopicRepository $topicRepository
     */
    public function __construct(
        TopicActionService $topicActionService,
        PostActionService $postActionService,
        CategoryRepository $categoryRepository,
        TopicRepository $topicRepository
    ) {
        $this->topicActionService = $topicActionService;
        $this->postActionService = $postActionService;
        $this->categoryRepository = $categoryRepository;
        $this->topicRepository = $topicRepository;
    }

    /**
     * @param Request $request
     * @param int $topicId
     * @return Response
     */
    public function topic(Request $request, int $topicId): Response
    {
        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getGameUser() !== null) {
            try {
                $this->postActionService->create($post, $topic, $this->getGameUser(), $request->getClientIp());
                $this->addFlash('success', 'Post added');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('forum/topic.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param int $categoryId
     * @return RedirectResponse|Response
     */
    public function create(Request $request, int $categoryId)
    {
        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            $this->addFlash('error', 'No such category!');
            return $this->redirectToRoute('Forum');
        }

        $topic = new Topic();
        $topic->setCategory($category);
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getGameUser() !== null) {
            try {
                $this->topicActionService->create($topic, $category, $this->getGameUser(), $request->getClientIp());
                $this->addFlash('success', 'Successfully created topic');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()], 302);
        }

        return $this->render('forum/topic_create.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @param int $topicId
     * @return RedirectResponse
     */
    public function remove(int $topicId): RedirectResponse
    {
        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $category = $topic->getCategory();
        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirectToRoute('Forum');
        }

        try {
            $this->topicActionService->remove($topic, $this->getGameUser());
            $this->addFlash('success', 'Topic removed');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Forum/Category', ['categoryId' => $category->getId()], 302);
    }

    /**
     * @param Request $request
     * @param int $topicId
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, int $topicId)
    {
        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()], 302);
        }

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->topicActionService->edit($topic, $this->getGameUser());
                $this->addFlash('success', 'Successfully edited topic');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()], 302);
        }

        return $this->render('forum/topic_edit.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }
}
