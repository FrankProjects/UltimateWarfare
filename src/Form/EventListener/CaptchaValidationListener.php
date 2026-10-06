<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\EventListener;

use FrankProjects\UltimateWarfare\Service\CaptchaGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CaptchaValidationListener implements EventSubscriberInterface
{
    private CaptchaGenerator $captchaGenerator;
    private string $invalidMessage = 'The captcha answer is incorrect.';

    public function __construct(CaptchaGenerator $captchaGenerator)
    {
        $this->captchaGenerator = $captchaGenerator;
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

        if (!$this->captchaGenerator->validateAnswer($data)) {
            $event->getForm()->addError(new FormError($this->invalidMessage));
        }
    }
}
