<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Form\Forum\PostType;
use FrankProjects\UltimateWarfare\Form\Forum\TopicType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TopicController extends BaseForumController
{
    /**
     * @param Request $request
     * @param int $topicId
     * @return Response
     */
    public function topic(Request $request, int $topicId): Response
    {
        $em = $this->getEm();
        $topic = $em->getRepository(Topic::class)
            ->find($topicId);

        if ($topic === null) {
            $this->addFlash('error', 'No such topic!');

            return $this->redirect($this->generateUrl('Forum'));
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getGameUser() !== null) {
            $lastPost = $em->getRepository('Game:Post')
                ->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            } else {
                $post->setTopic($topic);
                $post->setPosterIp($request->getClientIp());
                $post->setCreateDateTime(new \DateTime());
                $post->setUser($this->getGameUser());

                $em->persist($post);
                $em->flush();
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
        $em = $this->getEm();
        $category = $em->getRepository('Game:Category')
            ->find($categoryId);

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
            $lastPost = $em->getRepository('Game:Post')
                ->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum'));
            }

            $topic->setPosterIp($request->getClientIp());
            $topic->setCreateDateTime(new \DateTime());
            $topic->setUser($this->getGameUser());

            $em->persist($topic);
            $em->flush();

            $this->addFlash('success', 'Succesfully created topic');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        return $this->render('forum/topic_create.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param int $topicId
     * @return RedirectResponse
     */
    public function remove(Request $request, int $topicId): RedirectResponse
    {
        $em = $this->getEm();
        $topic = $em->getRepository('Game:Topic')
            ->find($topicId);

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
            $em->remove($post);
        }

        $em->remove($topic);
        $em->flush();
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
        $em = $this->getEm();
        $topic = $em->getRepository('Game:Topic')
            ->find($topicId);

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
            $lastPost = $em->getRepository('Game:Post')
                ->getLastPostByUser($this->getGameUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            }

            $topic->setEditUser($this->getGameUser());

            $em->persist($topic);
            $em->flush();

            $this->addFlash('success', 'Succesfully edited topic');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        return $this->render('forum/topic_edit.html.twig', [
            'topic' => $topic,
            'user' => $this->getGameUser(),
            'form' => $form->createView()
        ]);
    }
}
