<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\EventListener;

use FrankProjects\UltimateWarfare\Service\TurnstileVerifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CaptchaValidationListener implements EventSubscriberInterface
{
    private TurnstileVerifier $turnstileVerifier;
    private string $expectedAction = '';
    private string $invalidMessage = 'Verification failed.';

    public function __construct(TurnstileVerifier $turnstileVerifier)
    {
        $this->turnstileVerifier = $turnstileVerifier;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function setExpectedAction(string $action): self
    {
        $this->expectedAction = $action;

        return $this;
    }

    public function setInvalidMessage(string $message): self
    {
        $this->invalidMessage = $message;

        return $this;
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (!is_string($data) || $data === '') {
            $event->getForm()->addError(new FormError($this->invalidMessage));
            return;
        }

        if (!$this->turnstileVerifier->verify($data, $this->expectedAction)) {
            $event->getForm()->addError(new FormError($this->invalidMessage));
        }
    }
}
