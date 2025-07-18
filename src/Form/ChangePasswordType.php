<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;

/** @extends AbstractType<null> */
class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'oldPassword',
                PasswordType::class,
                [
                    'mapped' => false,
                    'label' => 'label.old_password',
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
                        'label' => 'label.password',
                    ],
                    'second_options' => [
                        'label' => 'label.password_repeat',
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
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.change_password'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => User::class,
                'translation_domain' => 'account'
            )
        );
    }
}
