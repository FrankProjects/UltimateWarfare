<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Form\EventListener\ReCaptchaValidationListener;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @phpstan-ignore missingType.generics */
class ReCaptchaType extends AbstractType
{
    /**
     * @var ReCaptcha
     */
    private ReCaptcha $reCaptcha;

    /**
     * ReCaptchaType constructor.
     *
     * @param ReCaptcha $reCaptcha
     */
    public function __construct(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var string $invalidMessage */
        $invalidMessage = $options['invalid_message'];

        $subscriber = new ReCaptchaValidationListener($this->reCaptcha);
        $subscriber->setInvalidMessage($invalidMessage);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['type'] = $options['type'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('type', 'invisible')
            ->setAllowedValues('type', ['checkbox', 'invisible']);

        $resolver
            ->setDefault('invalid_message', 'The captcha is invalid. Please try again.');
    }
}
