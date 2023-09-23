<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Form\Forum\PostType;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Service\Action\PostActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PostController extends BaseForumController
{
    private PostRepository $postRepository;
    private PostActionService $postActionService;

    public function __construct(
        PostRepository $postRepository,
        PostActionService $postActionService
    ) {
        $this->postRepository = $postRepository;
        $this->postActionService = $postActionService;
    }

    public function remove(int $postId): RedirectResponse
    {
        $this->ensureForumEnabled();

        $post = $this->postRepository->find($postId);

        if ($post === null) {
            $this->addFlash('error', 'No such post!');
            return $this->redirectToRoute('Forum');
        }

        $topic = $post->getTopic();

        try {
            $this->postActionService->remove($post, $this->getGameUser());
            $this->addFlash('success', 'Post removed');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()], 302);
    }

    public function edit(Request $request, int $postId): Response
    {
        $this->ensureForumEnabled();

        $post = $this->postRepository->find($postId);

        if ($post === null) {
            $this->addFlash('error', 'No such post!');
            return $this->redirectToRoute('Forum');
        }

        $topic = $post->getTopic();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getGameUser() !== null) {
            try {
                $this->postActionService->edit($post, $this->getGameUser());
                $this->addFlash('success', 'Successfully edited post');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('Forum/Topic', ['topicId' => $topic->getId()], 302);
        }

        return $this->render(
            'forum/post_edit.html.twig',
            [
                'topic' => $topic,
                'user' => $this->getGameUser(),
                'form' => $form->createView()
            ]
        );
    }
}
