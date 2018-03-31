<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Form\Forum\PostType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends BaseForumController
{
    /**
     * @param Request $request
     * @param int $postId
     * @return RedirectResponse
     */
    public function remove(Request $request, int $postId): RedirectResponse
    {
        $em = $this->getEm();
        $post = $em->getRepository('Game:Post')
            ->find($postId);

        if ($post === null) {
            $this->addFlash('error', 'No such post!');

            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = $post->getTopic();
        $user = $this->getUser();
        if ($user == null) {
            $this->addFlash('error', 'Not logged in!');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        if ($user->getId() != $post->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Not enough permissions!');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        $em->remove($post);
        $em->flush();
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
        $em = $this->getEm();
        $post = $em->getRepository('Game:Post')
            ->find($postId);

        if ($post === null) {
            $this->addFlash('error', 'No such post!');
            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = $post->getTopic();
        $user = $this->getUser();
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
            $lastPost = $em->getRepository('Game:Post')
                ->getLastPostByUser($this->getUser());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $this->addFlash('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            }

            $post->setEditUser($this->getUser());

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Succesfully edited post');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        return $this->render('forum/post_edit.html.twig', [
            'topic' => $topic,
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }
}
