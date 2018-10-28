<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\ChatLine;
use FrankProjects\UltimateWarfare\Entity\ChatUser;
use FrankProjects\UltimateWarfare\Repository\ChatLineRepository;
use FrankProjects\UltimateWarfare\Repository\ChatUserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

final class ChatController extends BaseController
{
    /**
     * @var ChatLineRepository
     */
    private $chatLineRepository;

    /**
     * @var ChatUserRepository
     */
    private $chatUserRepository;

    /**
     * ChatController constructor.
     *
     * @param ChatLineRepository $chatLineRepository
     * @param ChatUserRepository $chatUserRepository
     */
    public function __construct(
        ChatLineRepository $chatLineRepository,
        ChatUserRepository $chatUserRepository
    ) {
        $this->chatLineRepository = $chatLineRepository;
        $this->chatUserRepository = $chatUserRepository;
    }

    /**
     * @return Response
     */
    public function chat(): Response
    {
        $isGuest = true;
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            $chatName = $this->get('session')->get('chatName');

            if (!$chatName) {
                $chatName = uniqid('Guest_');
                $this->get('session')->set('chatName', $chatName);
            }
        } else {
            $isGuest = false;
            $chatName = $user->getUsername();
            $this->get('session')->set('chatName', $chatName);
        }

        return $this->render('site/chat.html.twig', [
            'chatName' => $chatName,
            'isGuest' => $isGuest
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getUsers(): JsonResponse
    {
        $chatUsers = $this->chatUserRepository->findInactiveChatUsers();

        if (count($chatUsers) > 0) {
            foreach ($chatUsers as $chatUser) {
                $text = $chatUser->getName() . ' left the chat';
                $chatLine = ChatLine::create('[System]', $text, time());
                $this->chatLineRepository->save($chatLine);
                $this->chatUserRepository->remove($chatUser);
            }
        }

        // With too many users the chat will freeze...
        $chatUsers = $this->chatUserRepository->findWithLimit(18);

        $chatUserArray = [];
        foreach ($chatUsers as $chatUser) {
            $chatUserArray[] = ['name' => $chatUser->getName()];
        }

        return $this->json([
            'users' => $chatUserArray,
            'total' => count($chatUsers)
        ]);
    }

    /**
     * @param int $lastChatLineId
     * @return JsonResponse
     */
    public function getChat(int $lastChatLineId): JsonResponse
    {
        $this->updateUser();

        // Deleting chats older than 30 minutes
        $chatLines = $this->chatLineRepository->findChatLinesOlderThanSeconds(1800);

        if (count($chatLines) > 0) {
            foreach ($chatLines as $chatLine) {
                $this->chatLineRepository->remove($chatLine);
            }
        }

        $chatLines = $this->chatLineRepository->findChatLinesByLastChatLineId($lastChatLineId);

        $chats = [];
        foreach ($chatLines as $chatLine) {
            $chat = [];
            $chat['id'] = $chatLine->getId();
            $chat['name'] = $chatLine->getName();
            $chat['text'] = $chatLine->getText();
            //$chat['text'] = filter_html($chatLine->getText());
            $chat['time'] = [
                'hours'   => date('H', $chatLine->getTimestamp()),
                'minutes' => date('i', $chatLine->getTimestamp())
            ];
            $chats[] = $chat;
        }

        return $this->json([
            'chats' => $chats
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addChat(Request $request): JsonResponse
    {
        $text = '';
        if ($request->getMethod() == 'POST') {
            $text = trim($request->request->get('chatText'));
        }

        if ($text == '') {
            return $this->json([]);
        }

        $chatName = $this->get('session')->get('chatName');

        $chatLine = ChatLine::create($chatName, $text, time());
        $this->chatLineRepository->save($chatLine);

        return $this->json([
            'status'   => 1,
            'insertID' => $chatLine->getId()
        ]);
    }

    /**
     * Update user activity
     */
    private function updateUser()
    {
        $chatName = $this->get('session')->get('chatName');

        $chatUser = $this->chatUserRepository->findByName($chatName);

        if ($chatUser) {
            $chatUser->setTimestampActivity(time());
            $this->chatUserRepository->save($chatUser);
        } else {
            $chatUser = ChatUser::create($chatName, time());
            $chatLine = ChatLine::create('[System]', $chatName .' joined the chat', time());
            $this->chatUserRepository->save($chatUser);
            $this->chatLineRepository->save($chatLine);
        }
    }
}
