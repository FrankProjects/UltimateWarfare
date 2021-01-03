<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Message;
use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Service\Action\MessageActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MessageController extends BaseGameController
{
    private MessageRepository $messageRepository;
    private MessageActionService $messageActionService;

    public function __construct(
        MessageRepository $messageRepository,
        MessageActionService $messageActionService
    ) {
        $this->messageRepository = $messageRepository;
        $this->messageActionService = $messageActionService;
    }

    public function inbox(Request $request): Response
    {
        /**
         * XXX TODO: Fix pagination
         */
        $player = $this->getPlayer();
        if ($player->getNotifications()->getMessage()) {
            $this->messageActionService->disableMessageNotification($player);
        }

        foreach ($this->getSelectedMessagesFromRequest($request) as $messageId) {
            $this->messageActionService->deleteMessageFromInbox($player, $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
        }

        $messages = $this->messageRepository->findNonDeletedMessagesToPlayer($player);

        return $this->render(
            'game/message/inbox.html.twig',
            [
                'player' => $player,
                'messages' => $messages
            ]
        );
    }

    public function inboxRead(int $messageId): Response
    {
        /**
         * XXX TODO: Fix smilies display
         */
        $player = $this->getPlayer();

        try {
            $message = $this->messageActionService->getMessageByIdAndToPlayer($messageId, $player);
            if ($message->getStatus() === Message::MESSAGE_STATUS_NEW) {
                $message->setStatus(Message::MESSAGE_STATUS_READ);
                $this->messageRepository->save($message);
            };
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/Message/Inbox');
        }

        return $this->render(
            'game/message/inboxRead.html.twig',
            [
                'player' => $player,
                'message' => $message
            ]
        );
    }

    public function inboxDelete(int $messageId): RedirectResponse
    {
        try {
            $this->messageActionService->deleteMessageFromInbox($this->getPlayer(), $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Message/Inbox');
    }

    public function outbox(Request $request): Response
    {
        /**
         * XXX TODO: Fix pagination
         */
        $player = $this->getPlayer();

        foreach ($this->getSelectedMessagesFromRequest($request) as $messageId) {
            $this->messageActionService->deleteMessageFromOutbox($player, $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
        }

        $messages = $this->messageRepository->findNonDeletedMessagesFromPlayer($player);

        return $this->render(
            'game/message/outbox.html.twig',
            [
                'player' => $player,
                'messages' => $messages
            ]
        );
    }

    public function outboxRead(int $messageId): Response
    {
        /**
         * XXX TODO: Fix smilies display
         */
        try {
            $message = $this->messageActionService->getMessageByIdAndFromPlayer($messageId, $this->getPlayer());
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/Message/Outbox');
        }

        return $this->render(
            'game/message/outboxRead.html.twig',
            [
                'player' => $this->getPlayer(),
                'message' => $message
            ]
        );
    }

    public function outboxDelete(int $messageId): RedirectResponse
    {
        try {
            $this->messageActionService->deleteMessageFromOutbox($this->getPlayer(), $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Message/Outbox');
    }

    public function newMessage(Request $request, $playerName = ''): Response
    {
        $player = $this->getPlayer();

        if ($playerName == '') {
            $playerName = $request->request->get('toPlayerName');
        }

        $adminMessage = (bool)$request->get('admin', false);

        if ($request->isMethod(Request::METHOD_POST)) {
            try {
                $this->messageActionService->sendMessage(
                    $player,
                    $request->get('subject'),
                    $request->get('message'),
                    $playerName,
                    $adminMessage
                );

                $this->addFlash('success', 'Message send!');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'game/message/new.html.twig',
            [
                'player' => $player,
                'toPlayerName' => $playerName,
                'subject' => $request->request->get('subject'),
                'message' => $request->request->get('message')
            ]
        );
    }

    private function getSelectedMessagesFromRequest(Request $request): array
    {
        $selectedMessages = [];

        if (
            $request->isMethod(Request::METHOD_POST) &&
            $request->get('del') !== null &&
            $request->get('selected_messages') !== null
        ) {
            foreach ($request->get('selected_messages') as $messageId) {
                $selectedMessages[] = intval($messageId);
            }
        }

        return $selectedMessages;
    }
}
