<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
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
            $request->getSession()->getFlashBag()->add('error', 'No such post!');

            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = $post->getTopic();
        $gameAccount = $this->getGameAccount();
        if ($gameAccount == null) {
            $request->getSession()->getFlashBag()->add('error', 'Not logged in!');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        if ($gameAccount->getId() != $post->getGameAccount()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $request->getSession()->getFlashBag()->add('error', 'Not enough permissions!');

            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        $em->remove($post);
        $em->flush();
        $request->getSession()->getFlashBag()->add('success', 'Post removed');
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
            $request->getSession()->getFlashBag()->add('error', 'No such post!');
            return $this->redirect($this->generateUrl('Forum'));
        }

        $topic = $post->getTopic();
        $gameAccount = $this->getGameAccount();
        if ($gameAccount == null) {
            $request->getSession()->getFlashBag()->add('error', 'Not logged in!');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        if ($gameAccount->getId() != $post->getGameAccount()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $request->getSession()->getFlashBag()->add('error', 'Not enough permissions!');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lastPost = $em->getRepository('Game:Post')
                ->getLastPostByGameAccount($this->getGameAccount());

            if ($lastPost !== null && $lastPost->getCreateDateTime() > new \DateTime('- 10 seconds')) {
                $request->getSession()->getFlashBag()->add('error', 'You can\'t mass post within 10 seconds!(Spam protection)');
                return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
            }

            $post->setEditGameAccount($this->getGameAccount());

            $em->persist($post);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Succesfully edited post');
            return $this->redirect($this->generateUrl('Forum/Topic', ['topicId' => $topic->getId()]));
        }

        return $this->render('forum/post_edit.html.twig', [
            'topic' => $topic,
            'gameAccount' => $this->getGameAccount(),
            'form' => $form->createView()
        ]);
    }
}
