<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

final class ChatController extends BaseController
{
    public function chat(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            $userId = 0;
        } else {
            $userId = $user->getId();
        }

        return $this->render('site/chat.html.twig', [
            'userId' => $userId,
            'token' => 'test',
            'websocketServerHost' => 'localhost',
            'websocketServerPort' => 8080
        ]);
    }
}
