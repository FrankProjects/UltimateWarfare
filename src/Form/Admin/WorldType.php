<?php

namespace FrankProjects\UltimateWarfare\Form\Admin;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Form\Admin\World\ResourcesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class WorldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.description',
                'attr' => array('cols' => 70, 'rows' => 8),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'label.status',
                'choices' => [
                    'label.created' => 0,
                    'label.running' => 1,
                    'label.finished' => 2
                ]
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'label.public',
                'required' => false
            ])
            ->add('maxPlayers', TextType::class, [
                'label' => 'label.maxPlayers'
            ])
            ->add('market', CheckboxType::class, [
                'label' => 'label.market',
                'required' => false
            ])
            ->add('federation', CheckboxType::class, [
                'label' => 'label.federation',
                'required' => false
            ])
            ->add('federationLimit', TextType::class, [
                'label' => 'label.federationLimit'
            ])
            ->add('resources', ResourcesType::class, [
                'label' => 'label.startResources',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.save'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => World::class,
            'translation_domain' => 'world'
        ]);
    }
}