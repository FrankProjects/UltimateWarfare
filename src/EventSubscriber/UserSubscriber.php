<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $user->setLastLogin(new \DateTime());
            $this->userRepository->save($user);
        } catch (\Exception $e) {
        }

        if ($user->getActive() === false) {
            $this->checkBannedAndRedirect($event);
        }
    }

    private function checkBannedAndRedirect(RequestEvent $event): void
    {
        if (
            !str_contains($event->getRequest()->getRequestUri(), '/game') &&
            !str_contains($event->getRequest()->getRequestUri(), '/forum')
        ) {
            return;
        }

        if (str_contains($event->getRequest()->getRequestUri(), '/game/banned')) {
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
