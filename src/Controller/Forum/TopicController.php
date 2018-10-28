<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Form\Forum\PostType;
use FrankProjects\UltimateWarfare\Form\Forum\TopicType;
use FrankProjects\UltimateWarfare\Repository\CategoryRepository;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TopicController extends BaseForumController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var TopicRepository
     */
    private $topicRepository;

    /**
     * TopicController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param PostRepository $postRepository
     * @param TopicRepository $topicRepository
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        PostRepository $postRepository,
        TopicRepository $topicRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
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

            return $this->redirect($this->generateUrl('Forum'));
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getGameUser() !== null) {
            $lastPost = $this->postRepository->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            } else {
                $post->setTopic($topic);
                $post->setPosterIp($request->getClientIp());
                $post->setCreateDateTime(new \DateTime());
                $post->setUser($this->getGameUser());

                $this->postRepository->save($post);
                $this->addFlash('success', 'Post added');
            }
        }

        return $this->render('forum/topic.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * XXX TODO: Fix max post limit
     * XXX TODO: Fix forum ban
     *
     * @param Request $request
     * @param int $categoryId
     * @return RedirectResponse|Response
     */
    public function create(Request $request, int $categoryId)
    {
        $category = $this->categoryRepository->find($categoryId);

        if ($category === null) {
            $this->addFlash('error', 'No such category!');
            return $this->redirect($this->generateUrl('Forum'));
        }

        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = new Topic();
        $topic->setCategory($category);

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lastPost = $this->postRepository->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum'));
            }

            $topic->setPosterIp($request->getClientIp());
            $topic->setCreateDateTime(new \DateTime());
            $topic->setUser($this->getGameUser());

            $this->topicRepository->save($topic);
            $this->addFlash('success', 'Successfully created topic');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
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

            return $this->redirect($this->generateUrl('Forum'));
        }

        $category = $topic->getCategory();
        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');

            return $this->redirect($this->generateUrl('Forum'));
        }

        if ($user->getId() != $topic->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Not enough permissions!');

            return $this->redirect($this->generateUrl('Forum'));
        }

        foreach ($topic->getPosts() as $post) {
            $this->postRepository->remove($post);
        }

        $this->topicRepository->remove($topic);
        $this->addFlash('success', 'Topic removed');

        return $this->redirect($this->generateUrl('Forum/Category', ['categoryId' => $category->getId()]));
    }

    /**
     * XXX TODO: Fix max post
     *
     * @param Request $request
     * @param int $topicId
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, int $topicId)
    {
        $topic = $this->topicRepository->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');
            return $this->redirect($this->generateUrl('Forum'));
        }

        $user = $this->getGameUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        if ($user->getId() != $topic->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Not enough permissions!');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lastPost = $this->postRepository->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            }

            $topic->setEditUser($this->getGameUser());

            $this->topicRepository->save($topic);
            $this->addFlash('success', 'Successfully edited topic');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        return $this->render('forum/topic_edit.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }
}
