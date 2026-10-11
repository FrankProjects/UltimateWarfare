<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/** @extends AbstractType<null> */
abstract class AbstractGameResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'cash',
                IntegerType::class,
                [
                    'label' => 'label.cash',
                    'constraints' => [
                        new PositiveOrZero()
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'food',
                IntegerType::class,
                [
                    'label' => 'label.food',
                    'constraints' => [
                        new PositiveOrZero()
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'wood',
                IntegerType::class,
                [
                    'label' => 'label.wood',
                    'constraints' => [
                        new PositiveOrZero()
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'steel',
                IntegerType::class,
                [
                    'label' => 'label.steel',
                    'constraints' => [
                        new PositiveOrZero()
                    ],
                    'empty_data' => '0'
                ]
            );
    }
}
