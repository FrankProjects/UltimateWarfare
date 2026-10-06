<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin\World;

use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<null> */
class MapConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'seed',
                TextType::class,
                [
                    'label' => 'label.seed',
                    'required' => false
                ]
            )
            ->add(
                'size',
                TextType::class,
                [
                    'label' => 'label.size'
                ]
            )
            ->add(
                'deepWaterLevel',
                RangeType::class,
                [
                    'label' => 'label.deepWaterLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                ]
            )
            ->add(
                'waterLevel',
                RangeType::class,
                [
                    'label' => 'label.waterLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                ]
            )
            ->add(
                'shallowWaterLevel',
                RangeType::class,
                [
                    'label' => 'label.shallowWaterLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                ]
            )
            ->add(
                'sandLevel',
                RangeType::class,
                [
                    'label' => 'label.sandLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                ]
            )
            ->add(
                'grasslandLevel',
                RangeType::class,
                [
                    'label' => 'label.grasslandLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                ]
            )
            ->add(
                'forestLevel',
                RangeType::class,
                [
                    'label' => 'label.forestLevel',
                    'attr' => ['min' => 100, 'max' => 250],
                ]
            )
            ->add(
                'hillsLevel',
                RangeType::class,
                [
                    'label' => 'label.hillsLevel',
                    'attr' => ['min' => 100, 'max' => 250],
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
