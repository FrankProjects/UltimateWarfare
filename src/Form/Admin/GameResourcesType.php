<?php

namespace FrankProjects\UltimateWarfare\Form\Admin;

use FrankProjects\UltimateWarfare\Entity\World\Resources;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GameResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cash', TextType::class, [
                'label' => 'label.cash'
            ])
            ->add('food', TextType::class, [
                'label' => 'label.food'
            ])
            ->add('wood', TextType::class, [
                'label' => 'label.wood'
            ])
            ->add('steel', TextType::class, [
                'label' => 'label.steel'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resources::class,
            'translation_domain' => 'gameresources'
        ]);
    }
}
