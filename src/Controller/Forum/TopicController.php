<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Exception\ForumDisabledException;
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
    private TopicActionService $topicActionService;
    private PostActionService $postActionService;
    private CategoryRepository $categoryRepository;
    private TopicRepository $topicRepository;

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

    public function topic(Request $request, int $topicId): Response
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->render('forum/forum_disabled.html.twig');
        }

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
                $this->postActionService->create($post, $topic, $this->getGameUser(), (string) $request->getClientIp());
                $this->addFlash('success', 'Post added');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'forum/topic.html.twig',
            [
                'topic' => $topic,
                'user' => $this->getGameUser(),
                'form' => $form->createView()
            ]
        );
    }

    public function create(Request $request, int $categoryId): Response
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->render('forum/forum_disabled.html.twig');
        }

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
                $this->topicActionService->create($topic, $category, $this->getGameUser(), (string) $request->getClientIp());
                $this->addFlash('success', 'Successfully created topic');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()]);
        }

        return $this->render(
            'forum/topic_create.html.twig',
            [
                'topic' => $topic,
                'user' => $this->getGameUser(),
                'form' => $form->createView()
            ]
        );
    }

    public function remove(int $topicId): RedirectResponse
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->redirectToRoute('Forum');
        }

        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $category = $topic->getCategory();
        $user = $this->getGameUser();
        if ($user === null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirectToRoute('Forum');
        }

        try {
            $this->topicActionService->remove($topic, $user);
            $this->addFlash('success', 'Topic removed');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Forum/Category', ['categoryId' => $category->getId()]);
    }

    public function edit(Request $request, int $topicId): Response
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->render('forum/forum_disabled.html.twig');
        }

        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $user = $this->getGameUser();
        if ($user === null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()]);
        }

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->topicActionService->edit($topic, $user);
                $this->addFlash('success', 'Successfully edited topic');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()]);
        }

        return $this->render(
            'forum/topic_edit.html.twig',
            [
                'topic' => $topic,
                'user' => $this->getGameUser(),
                'form' => $form->createView()
            ]
        );
    }

    public function sticky(int $topicId): RedirectResponse
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->redirectToRoute('Forum');
        }

        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $user = $this->getGameUser();
        if ($user === null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()]);
        }

        try {
            $this->topicActionService->sticky($topic, $user);
            $this->addFlash('success', 'Successfully changed topic to sticky');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Forum/Topic', ['topicId' => $topicId]);
    }

    public function unsticky(int $topicId): RedirectResponse
    {
        try {
            $this->ensureForumEnabled();
        } catch (ForumDisabledException) {
            return $this->redirectToRoute('Forum');
        }

        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirectToRoute('Forum');
        }

        $user = $this->getGameUser();
        if ($user === null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()]);
        }

        try {
            $this->topicActionService->unsticky($topic, $user);
            $this->addFlash('success', 'Successfully changed topic to unsticky');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Forum/Topic', ['topicId' => $topicId]);
    }
}
