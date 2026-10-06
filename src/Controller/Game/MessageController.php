<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Message;
use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Service\Action\MessageActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function sendMessageApi(Request $request): JsonResponse
    {
        try {
            $player = $this->getPlayer();

            /** @var array{toPlayerName?: string, subject?: string, message?: string} $data */
            $data = json_decode($request->getContent(), true) ?? [];

            $this->messageActionService->sendMessage(
                $player,
                $data['subject'] ?? '',
                $data['message'] ?? '',
                $data['toPlayerName'] ?? '',
                false
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Message sent!'
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function inboxApi(): JsonResponse
    {
        $player = $this->getPlayer();

        if ($player->getNotifications()->getMessage()) {
            $this->messageActionService->disableMessageNotification($player);
        }

        $messages = $this->messageRepository->findNonDeletedMessagesToPlayer($player);

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'from' => $message->getFromPlayer()->getName(),
                'subject' => $message->getSubject(),
                'date' => date('M d, Y H:i', $message->getTimestamp()),
                'timestamp' => $message->getTimestamp(),
                'isNew' => $message->getStatus() === Message::MESSAGE_STATUS_NEW,
                'isAdmin' => $message->getAdminMessage(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'messages' => $messagesData,
        ]);
    }

    public function outboxApi(): JsonResponse
    {
        $player = $this->getPlayer();
        $messages = $this->messageRepository->findNonDeletedMessagesFromPlayer($player);

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'to' => $message->getToPlayer()->getName(),
                'subject' => $message->getSubject(),
                'date' => date('M d, Y H:i', $message->getTimestamp()),
                'timestamp' => $message->getTimestamp(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'messages' => $messagesData,
        ]);
    }

    public function readMessageApi(int $messageId): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $message = $this->messageActionService->getMessageByIdAndToPlayer($messageId, $player);

            if ($message->getStatus() === Message::MESSAGE_STATUS_NEW) {
                $message->setStatus(Message::MESSAGE_STATUS_READ);
                $this->messageRepository->save($message);
            }

            return new JsonResponse([
                'success' => true,
                'message' => [
                    'id' => $message->getId(),
                    'from' => $message->getFromPlayer()->getName(),
                    'subject' => $message->getSubject(),
                    'body' => $message->getMessage(),
                    'date' => date('M d, Y H:i', $message->getTimestamp()),
                    'isAdmin' => $message->getAdminMessage(),
                ],
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function readOutboxMessageApi(int $messageId): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $message = $this->messageActionService->getMessageByIdAndFromPlayer($messageId, $player);

            return new JsonResponse([
                'success' => true,
                'message' => [
                    'id' => $message->getId(),
                    'to' => $message->getToPlayer()->getName(),
                    'subject' => $message->getSubject(),
                    'body' => $message->getMessage(),
                    'date' => date('M d, Y H:i', $message->getTimestamp()),
                ],
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteInboxMessageApi(int $messageId): JsonResponse
    {
        try {
            $this->messageActionService->deleteMessageFromInbox($this->getPlayer(), $messageId);

            return new JsonResponse([
                'success' => true,
                'message' => 'Message deleted!'
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteOutboxMessageApi(int $messageId): JsonResponse
    {
        try {
            $this->messageActionService->deleteMessageFromOutbox($this->getPlayer(), $messageId);

            return new JsonResponse([
                'success' => true,
                'message' => 'Message deleted!'
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
