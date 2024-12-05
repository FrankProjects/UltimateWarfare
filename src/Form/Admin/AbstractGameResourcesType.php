<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/** @phpstan-ignore missingType.generics */
abstract class AbstractGameResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'cash',
                NumberType::class,
                [
                    'label' => 'label.cash'
                ]
            )
            ->add(
                'food',
                NumberType::class,
                [
                    'label' => 'label.food'
                ]
            )
            ->add(
                'wood',
                NumberType::class,
                [
                    'label' => 'label.wood'
                ]
            )
            ->add(
                'steel',
                NumberType::class,
                [
                    'label' => 'label.steel'
                ]
            );
    }
}
