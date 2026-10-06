<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserSubscriber extends AbstractUserSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private UserRepository $userRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        UserRepository $userRepository
    ) {
        parent::__construct($tokenStorage);
        $this->router = $router;
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null) {
            return;
        }

        try {
            $user->setLastSeenAt(new \DateTime());
            $user->setLastSeenIp($event->getRequest()->getClientIp());
            $this->userRepository->save($user);
        } catch (\Exception $e) {
        }

        if ($user->isBanned()) {
            $this->checkBannedAndRedirect($event);
        }
    }

    private function checkBannedAndRedirect(RequestEvent $event): void
    {
        $path = $event->getRequest()->getPathInfo();
        $isGamePath = str_starts_with($path, '/game/');
        $isForumPath = $path === '/forum' || str_starts_with($path, '/forum/');

        if (!$isGamePath && !$isForumPath) {
            return;
        }

        if ($path === '/game/banned') {
            return;
        }

        if (str_starts_with($path, '/game/api/')) {
            $event->setResponse(new JsonResponse(
                ['success' => false, 'message' => 'Your account has been banned.'],
                Response::HTTP_FORBIDDEN
            ));
            return;
        }

        $response = new RedirectResponse(
            $this->router->generate('Game/Banned', [], UrlGeneratorInterface::ABSOLUTE_PATH)
        );
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
