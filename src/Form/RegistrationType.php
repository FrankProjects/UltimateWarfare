<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/** @phpstan-ignore missingType.generics */
class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'label.email'
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'label.username'
                ]
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'label.password'
                    ],
                    'second_options' => [
                        'label' => 'label.password_repeat'
                    ]
                ]
            )
            ->add(
                'agreeTerms',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'label' => 'label.accept_rules'
                ]
            )
            ->add(
                'captcha',
                ReCaptchaType::class,
                [
                    'mapped' => false,
                    'type' => 'checkbox' // (invisible, checkbox)
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.register'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'translation_domain' => 'register'
            ]
        );
    }
}
