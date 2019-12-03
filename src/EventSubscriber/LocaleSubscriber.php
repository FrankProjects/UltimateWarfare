<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $validLocales = [
        'en',
        'nl'
    ];

    /**
     * LocaleSubscriber constructor.
     *
     * @param string $defaultLocale
     */
    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMasterRequest() || !$request->hasPreviousSession()) {
            return;
        }

        $locale = $request->query->get('_locale');
        if ($locale !== null && in_array($locale, $this->validLocales)) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => array(array('onKernelRequest', 20))
        ];
    }
}
