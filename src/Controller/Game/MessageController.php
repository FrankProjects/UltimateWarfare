<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Message;
use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MessageController extends BaseGameController
{
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * MessageController constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(
        PlayerRepository $playerRepository,
        MessageRepository $messageRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->messageRepository = $messageRepository;
    }

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

        $messages = $this->messageRepository->findNonDeletedMessagesToPlayer($player);

        return $this->render('game/message/inbox.html.twig', [
            'player' => $player,
            'messages' => $messages
        ]);
    }

    /**
     * XXX TODO: Fix smilies display
     *
     * @param int $messageId
     * @return Response
     */
    public function inboxRead(int $messageId): Response
    {
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            $this->addFlash('error', 'No such message');
            return $this->redirectToRoute('Game/Message/Inbox');
        }

        if ($message->getToPlayer()->getId() !== $this->getPlayer()->getId()) {
            $this->addFlash('error', 'This is not your message!');
            return $this->redirectToRoute('Game/Message/Inbox');
        }

        return $this->render('game/message/inboxRead.html.twig', [
            'player' => $this->getPlayer(),
            'message' => $message
        ]);
    }

    /**
     * @param int $messageId
     * @return Response
     * @throws \Exception
     */
    public function inboxDelete(int $messageId): Response
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
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            $this->addFlash('error', 'No such message');
            return;
        }

        if ($message->getToPlayer()->getId() !== $this->getPlayer()->getId()) {
            $this->addFlash('error', 'This is not your message!');
            return;
        }

        $message->setToDelete(true);
        $this->messageRepository->save($message);

        $this->addFlash('success', 'Message successfully deleted!');
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

        $messages = $this->messageRepository->findNonDeletedMessagesFromPlayer($player);

        return $this->render('game/message/outbox.html.twig', [
            'player' => $player,
            'messages' => $messages
        ]);
    }

    /**
     * XXX TODO: Fix smilies display
     *
     * @param int $messageId
     * @return Response
     */
    public function outboxRead(int $messageId): Response
    {
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            $this->addFlash('error', 'No such message');
            return $this->redirectToRoute('Game/Message/Outbox');
        }

        if ($message->getFromPlayer()->getId() !== $this->getPlayer()->getId()) {
            $this->addFlash('error', 'This is not your message!');
            return $this->redirectToRoute('Game/Message/Outbox');
        }

        return $this->render('game/message/outboxRead.html.twig', [
            'player' => $this->getPlayer(),
            'message' => $message
        ]);
    }

    /**
     * @param int $messageId
     * @return Response
     * @throws \Exception
     */
    public function outboxDelete(int $messageId): Response
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
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            $this->addFlash('error', 'No such message');
            return;
        }

        if ($message->getFromPlayer()->getId() !== $this->getPlayer()->getId()) {
            $this->addFlash('error', 'This is not your message!');
            return;
        }

        $message->setFromDelete(true);
        $this->messageRepository->save($message);

        $this->addFlash('success', 'Message successfully deleted!');
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

        $toPlayer = $this->playerRepository->findByNameAndWorld($request->request->get('to'), $this->getPlayer()->getWorld());

        if (!$toPlayer) {
            $this->addFlash('error', 'No such player');
            return;
        }

        // XXX TODO: Fix permissions checking for admin, always disable for now...
        $adminMessage = false;

        $message = Message::create($this->getPlayer(), $toPlayer, $subject, $message, $adminMessage);
        $toPlayer->setMessage($toPlayer->getMessage() + 1);
        $this->messageRepository->save($message);
        $this->playerRepository->save($toPlayer);

        $this->addFlash('success', 'Message send!');
    }
}
