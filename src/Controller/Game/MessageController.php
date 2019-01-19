<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Service\Action\MessageActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MessageController extends BaseGameController
{
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
     * @param MessageRepository $messageRepository
     * @param MessageActionService $messageActionService
     */
    public function __construct(
        MessageRepository $messageRepository,
        MessageActionService $messageActionService
    ) {
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

        foreach ($this->getSelectedMessagesFromRequest($request) as $messageId) {
            $this->messageActionService->deleteMessageFromInbox($player, $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
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
        $player = $this->getPlayer();

        try {
            $message = $this->messageActionService->getMessageByIdAndToPlayer($messageId, $player);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/Message/Inbox');
        }

        return $this->render('game/message/inboxRead.html.twig', [
            'player' => $player,
            'message' => $message
        ]);
    }

    /**
     * @param int $messageId
     * @return RedirectResponse
     */
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

        foreach ($this->getSelectedMessagesFromRequest($request) as $messageId) {
            $this->messageActionService->deleteMessageFromOutbox($player, $messageId);
            $this->addFlash('success', 'Message successfully deleted!');
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
        try {
            $message = $this->messageActionService->getMessageByIdAndFromPlayer($messageId, $this->getPlayer());
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/Message/Outbox');
        }

        return $this->render('game/message/outboxRead.html.twig', [
            'player' => $this->getPlayer(),
            'message' => $message
        ]);
    }

    /**
     * @param int $messageId
     * @return RedirectResponse
     */
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

        if ($request->isMethod(Request::METHOD_POST)) {
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

    /**
     * @param Request $request
     * @return array
     */
    private function getSelectedMessagesFromRequest(Request $request): array
    {
        $selectedMessages = [];

        if ($request->isMethod(Request::METHOD_POST) &&
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
