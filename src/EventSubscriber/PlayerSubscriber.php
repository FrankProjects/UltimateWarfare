<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PlayerSubscriber extends AbstractUserSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * PlayerSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        PlayerRepository $playerRepository
    ) {
        parent::__construct($tokenStorage);
        $this->router = $router;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null) {
            return;
        }

        if ($user->getActive() === false) {
            $this->checkBannedAndRedirect($event);
        }
    }

    /**
     * @param GetResponseEvent $event
     */
    private function checkBannedAndRedirect(GetResponseEvent $event): void
    {
        if (
            strpos($event->getRequest()->getRequestUri(), '/game') === false &&
            strpos($event->getRequest()->getRequestUri(), '/forum') === false
        ) {
            return;
        }

        if (strpos($event->getRequest()->getRequestUri(), '/game/banned') !== false) {
            return;
        }

        $response = new RedirectResponse(
            $this->router->generate('Game/Banned', [], UrlGeneratorInterface::ABSOLUTE_PATH)
        );
        $event->setResponse($response);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
