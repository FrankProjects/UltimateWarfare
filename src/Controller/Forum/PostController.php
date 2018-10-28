<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Form\Forum\PostType;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends BaseForumController
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * PostController constructor.
     *
     * @param PostRepository $postRepository
     */
    public function __construct(
        PostRepository $postRepository
    ) {
        $this->postRepository = $postRepository;
    }

    /**
     * @param int $postId
     * @return RedirectResponse
     */
    public function remove(int $postId): RedirectResponse
    {
        $post = $this->postRepository->find($postId);

        if ($post === null) {
            $this->addFlash('error', 'No such post!');

            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = $post->getTopic();
        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        if ($user->getId() != $post->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Not enough permissions!');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        $this->postRepository->remove($post);
        $this->addFlash('success', 'Post removed');

        return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
    }

    /**
     * @param Request $request
     * @param int $postId
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, int $postId)
    {
        $post = $this->postRepository->find($postId);

        if ($post === null) {
            $this->addFlash('error', 'No such post!');
            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = $post->getTopic();
        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        if ($user->getId() != $post->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Not enough permissions!');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lastPost = $this->postRepository->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            }

            $post->setEditUser($this->getGameUser());

            $this->postRepository->save($post);
            $this->addFlash('success', 'Successfully edited post');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        return $this->render('forum/post_edit.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }
}
