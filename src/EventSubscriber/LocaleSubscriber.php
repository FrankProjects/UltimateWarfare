<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;
    /**
     * @var array<int, string>
     */
    private array $validLocales = [
        'en',
        'nl'
    ];

    public function __construct(string $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$request->hasPreviousSession()) {
            return;
        }

        $locale = $request->query->get('_locale');
        if ($locale !== null && in_array($locale, $this->validLocales, true)) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            /** @var string $sessionLocale */
            $sessionLocale = $request->getSession()->get('_locale', $this->defaultLocale);
            $request->setLocale($sessionLocale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => array(array('onKernelRequest', 20))
        ];
    }
}
