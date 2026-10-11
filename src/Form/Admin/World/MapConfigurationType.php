<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin\World;

use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

/** @extends AbstractType<null> */
class MapConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'seed',
                IntegerType::class,
                [
                    'label' => 'label.seed',
                    'required' => false,
                    'constraints' => [
                        new PositiveOrZero()
                    ],
                    // A seed of 0 makes WorldGeneratorService pick a random seed
                    'empty_data' => '0'
                ]
            )
            ->add(
                'size',
                IntegerType::class,
                [
                    'label' => 'label.size',
                    'constraints' => [
                        new Positive()
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'deepWaterLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.deepWaterLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'waterLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.waterLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'shallowWaterLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.shallowWaterLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'sandLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.sandLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'grasslandLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.grasslandLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'forestLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.forestLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'hillsLevel',
                IntegerType::class,
                [
                    'block_prefix' => 'range',
                    'label' => 'label.hillsLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                    'constraints' => [
                        new Range(min: 100, max: 250)
                    ],
                    'empty_data' => '0'
                ]
            )
            ->add(
                'save',
                CheckboxType::class,
                [
                    'label' => 'label.save',
                    'mapped' => false,
                    'required' => false
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.generate'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MapConfiguration::class,
                'translation_domain' => 'world'
            ]
        );
    }
}
