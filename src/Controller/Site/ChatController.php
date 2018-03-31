<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\ChatLine;
use FrankProjects\UltimateWarfare\Entity\ChatUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

final class ChatController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function chat(Request $request): Response
    {
        $isGuest = true;
        $user = $this->getGameUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            $chatName = $this->get('session')->get('chatName');

            if(!$chatName){
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
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsers(Request $request): JsonResponse
    {
        $em = $this->getEm();

        // Remove inactive chat users
        $chatUsers = $em->getRepository('Game:ChatUser')
            ->findInactiveChatUsers();

        if (count($chatUsers) > 0) {
            foreach ($chatUsers as $chatUser) {
                $text = $chatUser->getName() . ' left the chat';
                $chatLine = ChatLine::create('[System]', $text, time());
                $em->persist($chatLine);
                $em->remove($chatUser);
            }

            $em->flush();
        }


        // With too many users the chat will freeze...
        $limitUsers = 18;
        $chatUsers = $em->getRepository('Game:ChatUser')
            ->findBy(
                [],
                ['name' => 'ASC'],
                $limitUsers
            );

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
     * @param Request $request
     * @param int $lastChatLineId
     * @return JsonResponse
     */
    public function getChat(Request $request, int $lastChatLineId): JsonResponse
    {
        $em = $this->getEm();

        $this->updateUser();

        // Deleting chats older than 30 minutes
        $chatLines = $em->getRepository('Game:ChatLine')
            ->findChatLinesOlderThanSeconds(1800);

        if (count($chatLines) > 0) {
            foreach ($chatLines as $chatLine) {
                $em->remove($chatLine);
            }

            $em->flush();
        }

        $chatLines = $em->getRepository('Game:ChatLine')
            ->findChatLinesByLastChatLineId($lastChatLineId);

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

        $em = $this->getEm();
        $chatLine = ChatLine::create($chatName, $text, time());
        $em->persist($chatLine);
        $em->flush();

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

        $em = $this->getEm();
        $chatUser = $em->getRepository('Game:ChatUser')
            -> findOneBy(['name' => $chatName]);

        if ($chatUser) {
            $chatUser->setTimestampActivity(time());
            $em->persist($chatUser);
            $em->flush();
        } else {
            $chatUser = ChatUser::create($chatName, time());
            $chatLine = ChatLine::create('[System]', $chatName .' joined the chat', time());
            $em->persist($chatUser);
            $em->persist($chatLine);
            $em->flush();
        }
    }
}
