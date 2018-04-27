<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MessageController extends BaseGameController
{
    /**
     * XXX TODO: Fix pagination
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function inbox(Request $request): Response
    {
        $player = $this->getPlayer();

        if ($request->getMethod() == 'POST') {
            if ($request->request->get('action') == 'delete' && $request->request->get('message') != null) {
                $this->deleteMessageFromInbox($request->request->get('message'));
            }

            if ($request->request->get('del') && $request->request->get('selected_messages') != null) {
                foreach ($request->request->get('selected_messages') as $messageId) {
                    $this->deleteMessageFromInbox($messageId);
                }
            }
        }

        $em = $this->getEm();
        $messages = $em->getRepository('Game:Message')
            ->findNonDeletedMessagesToPlayer($player);

        return $this->render('game/message/inbox.html.twig', [
            'player' => $player,
            'messages' => $messages
        ]);
    }

    /**
     * XXX TODO: Fix smilies display
     *
     * @param Request $request
     * @param int $messageId
     * @return Response
     */
    public function inboxRead(Request $request, int $messageId): Response
    {
        $em = $this->getEm();
        $message = $em->getRepository('Game:Message')
            ->findOneBy(['id' => $messageId, 'toPlayer' => $this->getPlayer()]);

        if (!$message) {
            $this->addFlash('error', 'No such message');
            return $this->redirectToRoute('Game/Message/Inbox');
        }

        return $this->render('game/message/inboxRead.html.twig', [
            'player' => $this->getPlayer(),
            'message' => $message
        ]);
    }

    /**
     * @param Request $request
     * @param int $messageId
     * @return Response
     * @throws \Exception
     */
    public function inboxDelete(Request $request, int $messageId): Response
    {
        $this->deleteMessageFromInbox($messageId);

        return $this->redirectToRoute('Game/Message/Inbox');
    }

    /**
     * @param int $messageId
     * @throws \Exception
     */
    private function deleteMessageFromInbox(int $messageId)
    {
        $em = $this->getEm();
        $message = $em->getRepository('Game:Message')
            ->findOneBy(['id' => $messageId, 'toPlayer' => $this->getPlayer()]);
        if (!$message) {
            $this->addFlash('error', 'No such message');
            return;
        }

        $message->setToDelete(true);
        $em->persist($message);
        $em->flush();
        $this->addFlash('success', 'Message succesfully deleted!');
    }

    /**
     * XXX TODO: Fix pagination
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function outbox(Request $request): Response
    {
        $player = $this->getPlayer();

        if ($request->getMethod() == 'POST') {
            if ($request->request->get('action') == 'delete' && $request->request->get('message') != null) {
                $this->deleteMessageFromOutbox($request->request->get('message'));
            }

            if ($request->request->get('del') && $request->request->get('selected_messages') != null) {
                foreach ($request->request->get('selected_messages') as $messageId) {
                    $this->deleteMessageFromOutbox($messageId);
                }
            }
        }

        $em = $this->getEm();
        $messages = $em->getRepository('Game:Message')
            ->findNonDeletedMessagesFromPlayer($player);

        return $this->render('game/message/outbox.html.twig', [
            'player' => $player,
            'messages' => $messages
        ]);
    }

    /**
     * XXX TODO: Fix smilies display
     *
     * @param Request $request
     * @param int $messageId
     * @return Response
     */
    public function outboxRead(Request $request, int $messageId): Response
    {
        $em = $this->getEm();
        $message = $em->getRepository('Game:Message')
            ->findOneBy(['id' => $messageId, 'fromPlayer' => $this->getPlayer()]);

        if (!$message) {
            $this->addFlash('error', 'No such message');
            return $this->redirectToRoute('Game/Message/Outbox');
        }

        return $this->render('game/message/outboxRead.html.twig', [
            'player' => $this->getPlayer(),
            'message' => $message
        ]);
    }

    /**
     * @param Request $request
     * @param int $messageId
     * @return Response
     * @throws \Exception
     */
    public function outboxDelete(Request $request, int $messageId): Response
    {
        $this->deleteMessageFromOutbox($messageId);

        return $this->redirectToRoute('Game/Message/Outbox');
    }

    /**
     * @param int $messageId
     * @throws \Exception
     */
    private function deleteMessageFromOutbox(int $messageId)
    {
        $em = $this->getEm();
        $message = $em->getRepository('Game:Message')
            ->findOneBy(['id' => $messageId, 'fromPlayer' => $this->getPlayer()]);
        if (!$message) {
            $this->addFlash('error', 'No such message');
            return;
        }

        $message->setFromDelete(true);
        $em->persist($message);
        $em->flush();
        $this->addFlash('success', 'Message succesfully deleted!');
    }

    /**
     * @param Request $request
     * @param string $playerName
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, $playerName = ''): Response
    {
        $player = $this->getPlayer();

        if ($playerName == '') {
            $playerName = $request->request->get('toPlayerName');
        }

        if ($request->getMethod() == 'POST') {
            if ($request->request->get('submit')) {
                $this->sendMessage($request);
            }
        }

        return $this->render('game/message/new.html.twig', [
            'player' => $player,
            'toPlayerName' => $playerName,
            'subject' => $request->request->get('subject'),
            'message' => $request->request->get('message')
        ]);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    private function sendMessage(Request $request)
    {
        $subject = trim($request->request->get('subject'));
        if ($subject == '') {
            $this->addFlash('error', 'Please type a subject');
            return;
        }

        $message = trim($request->request->get('message'));
        if ($message == '') {
            $this->addFlash('error', 'Please type a message');
            return;
        }

        $em = $this->getEm();
        $toPlayer = $em->getRepository('Game:Player')
            ->findOneBy(['name' => $request->request->get('to'), 'world' => $this->getPlayer()->getWorld()]);
        if (!$toPlayer) {
            $this->addFlash('error', 'No such player');
            return;
        }

        // XXX TODO: Fix permissions checking for admin, always disable for now...
        $adminMessage = false;

        $message = Message::create($this->getPlayer(), $toPlayer, $subject, $message, $adminMessage);
        $toPlayer->setMessage($toPlayer->getMessage() + 1);
        $em->persist($message);
        $em->persist($toPlayer);
        $em->flush();

        $this->addFlash('success', 'Message send!');
    }
}
