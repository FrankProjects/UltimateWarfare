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
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $locale = $request->query->get('_locale');

        if (is_string($locale) && in_array($locale, $this->validLocales, true)) {
            $request->setLocale($locale);

            if ($request->hasSession()) {
                $request->getSession()->set('_locale', $locale);
            }

            return;
        }

        if ($request->hasPreviousSession()) {
            // if no explicit locale has been set on this request, use one from the session
            /** @var string $sessionLocale */
            $sessionLocale = $request->getSession()->get('_locale', $this->defaultLocale);
            $request->setLocale($sessionLocale);
            return;
        }

        $request->setLocale($this->defaultLocale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => array(array('onKernelRequest', 20))
        ];
    }
}
