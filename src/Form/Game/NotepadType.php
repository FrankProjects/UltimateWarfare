<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<null> */
class NotepadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'notepad',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'rows' => 15,
                        'cols' => 80
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.update'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Player::class,
                'translation_domain' => 'notepad'
            )
        );
    }
}
