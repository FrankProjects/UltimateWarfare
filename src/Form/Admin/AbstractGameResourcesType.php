<?php

namespace FrankProjects\UltimateWarfare\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractGameResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'cash',
                TextType::class,
                [
                    'label' => 'label.cash'
                ]
            )
            ->add(
                'food',
                TextType::class,
                [
                    'label' => 'label.food'
                ]
            )
            ->add(
                'wood',
                TextType::class,
                [
                    'label' => 'label.wood'
                ]
            )
            ->add(
                'steel',
                TextType::class,
                [
                    'label' => 'label.steel'
                ]
            );
    }
}
