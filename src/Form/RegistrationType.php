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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/** @extends AbstractType<null> */
class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'label.email',
                    'constraints' => [
                        new NotBlank(),
                        new Email()
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'label.username',
                    'constraints' => [
                        new Length(min: 5)
                    ],
                    'empty_data' => ''
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
                    ],
                    'constraints' => [
                        new Length(
                            min: 8,
                            max: 4096,
                            minMessage: 'Password must have at least 8 characters',
                        ),
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'agreeTerms',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'label' => 'label.accept_rules',
                    'constraints' => [
                        new IsTrue(
                            message: 'You must agree to our terms.'
                        )
                    ]
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
