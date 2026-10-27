<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/** @extends AbstractType<null> */
class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'label.password',
                        'translation_domain' => 'register'
                    ],
                    'second_options' => [
                        'label' => 'label.password_repeat',
                        'translation_domain' => 'register'
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Length(
                            min: 8,
                            max: 4096,
                            minMessage: 'Password must have at least 8 characters',
                        ),
                    ]
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.submit',
                    'translation_domain' => 'reset_password'
                ]
            );
    }
}
