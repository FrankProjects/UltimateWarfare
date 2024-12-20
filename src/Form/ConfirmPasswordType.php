<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/** @extends AbstractType<null> */
class ConfirmPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                    'mapped' => false,
                    'label' => 'label.password',
                    'translation_domain' => 'account'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => User::class,
            )
        );
    }
}
