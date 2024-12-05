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

/** @phpstan-ignore missingType.generics */
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
                'waterLevel',
                RangeType::class,
                [
                    'label' => 'label.waterLevel',
                    'attr' => array('min' => 0, 'max' => 1000),
                ]
            )
            ->add(
                'beachLevel',
                RangeType::class,
                [
                    'label' => 'label.beachLevel',
                    'attr' => array('min' => 0, 'max' => 1000),
                ]
            )
            ->add(
                'forrestLevel',
                RangeType::class,
                [
                    'label' => 'label.forrestLevel',
                    'attr' => array('min' => 0, 'max' => 1000),
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
