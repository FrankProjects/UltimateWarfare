<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin;

use FrankProjects\UltimateWarfare\Entity\GameNews;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;

/** @extends AbstractType<null> */
class GameNewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'label.title',
                    'constraints' => [
                        new Length(min: 4)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => 'label.message',
                    'attr' => array('cols' => 70, 'rows' => 8),
                    'constraints' => [
                        new Length(min: 4)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'label.enabled',
                    'required' => false
                ]
            )
            ->add(
                'mainpage',
                CheckboxType::class,
                [
                    'label' => 'label.mainpage',
                    'required' => false
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'label.save'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => GameNews::class,
                'translation_domain' => 'gamenews'
            ]
        );
    }
}
