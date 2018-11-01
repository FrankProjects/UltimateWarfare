<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\Action\MessageActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
     * @var MessageActionService
     */
    private $messageActionService;

    /**
     * MessageController constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param MessageRepository $messageRepository
     * @param MessageActionService $messageActionService
     */
    public function __construct(
        PlayerRepository $playerRepository,
        MessageRepository $messageRepository,
        MessageActionService $messageActionService
    ) {
        $this->playerRepository = $playerRepository;
        $this->messageRepository = $messageRepository;
        $this->messageActionService = $messageActionService;
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

        if ($request->isMethod('POST') &&
            $request->get('del') !== null &&
            $request->get('selected_messages') !== null
        ) {
            foreach ($request->get('selected_messages') as $messageId) {
                $messageId = intval($messageId);
                $this->messageActionService->deleteMessageFromInbox($player, $messageId);
                $this->addFlash('success', 'Message successfully deleted!');
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
     */
    public function inboxDelete(int $messageId): Response
    {
        try {
            $this->messageActionService->deleteMessageFromInbox($this->getPlayer(), $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Message/Inbox');
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

        if ($request->isMethod('POST') &&
            $request->get('del') !== null &&
            $request->get('selected_messages') !== null
        ) {
            foreach ($request->get('selected_messages') as $messageId) {
                $messageId = intval($messageId);
                $this->messageActionService->deleteMessageFromOutbox($player, $messageId);
                $this->addFlash('success', 'Message successfully deleted!');
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
     */
    public function outboxDelete(int $messageId): Response
    {
        try {
            $this->messageActionService->deleteMessageFromOutbox($this->getPlayer(), $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Message/Outbox');
    }

    /**
     * @param Request $request
     * @param string $playerName
     * @return Response
     */
    public function newMessage(Request $request, $playerName = ''): Response
    {
        $player = $this->getPlayer();

        if ($playerName == '') {
            $playerName = $request->request->get('toPlayerName');
        }

        if ($request->isMethod('POST')) {
            try {
                $this->messageActionService->sendMessage($player, $request->get('subject'), $request->get('message'), $playerName);

                $this->addFlash('success', 'Message send!');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('game/message/new.html.twig', [
            'player' => $player,
            'toPlayerName' => $playerName,
            'subject' => $request->request->get('subject'),
            'message' => $request->request->get('message')
        ]);
    }
}
