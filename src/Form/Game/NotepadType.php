<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Game;

use FrankProjects\UltimateWarfare\Entity\player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotepadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notepad', TextareaType::class, [
                'label' => 'label.notepad',
                'translation_domain' => 'notepad',
                'attr' => [
                    'rows' => 15,
                    'cols' => 80
                    ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Player::class,
        ));
    }
}
