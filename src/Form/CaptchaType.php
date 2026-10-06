<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Form\EventListener\CaptchaValidationListener;
use FrankProjects\UltimateWarfare\Service\CaptchaGenerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<null> */
class CaptchaType extends AbstractType
{
    public function __construct(
        private readonly CaptchaGenerator $captchaGenerator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var string $invalidMessage */
        $invalidMessage = $options['invalid_message'];

        $subscriber = new CaptchaValidationListener($this->captchaGenerator);
        $subscriber->setInvalidMessage($invalidMessage);
        $builder->addEventSubscriber($subscriber);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('invalid_message', 'captcha.invalid');
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
