<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Form\EventListener\CaptchaValidationListener;
use FrankProjects\UltimateWarfare\Service\TurnstileVerifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<null> */
class CaptchaType extends AbstractType
{
    public function __construct(
        private readonly TurnstileVerifier $turnstileVerifier,
        private readonly string $siteKey
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var string $invalidMessage */
        $invalidMessage = $options['invalid_message'];
        /** @var string $turnstileAction */
        $turnstileAction = $options['turnstile_action'];

        $subscriber = new CaptchaValidationListener($this->turnstileVerifier);
        $subscriber->setExpectedAction($turnstileAction);
        $subscriber->setInvalidMessage($invalidMessage);
        $builder->addEventSubscriber($subscriber);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['site_key'] = $this->siteKey;
        $view->vars['turnstile_action'] = $options['turnstile_action'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('invalid_message', 'captcha.invalid')
            ->setRequired('turnstile_action')
            ->setAllowedTypes('turnstile_action', 'string');
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'captcha';
    }
}
