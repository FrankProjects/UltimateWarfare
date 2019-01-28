<?php

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractBattleStatsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attack', TextType::class, [
                'label' => 'label.attack'
            ])
            ->add('attackSpeed', TextType::class, [
                'label' => 'label.attackSpeed'
            ])
            ->add('defence', TextType::class, [
                'label' => 'label.defence'
            ])
            ->add('defenceSpeed', TextType::class, [
                'label' => 'label.defenceSpeed'
            ]);
    }
}
