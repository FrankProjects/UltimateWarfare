<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/** @extends AbstractType<null> */
abstract class AbstractBattleStatsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'attack',
                NumberType::class,
                [
                    'label' => 'label.attack'
                ]
            )
            ->add(
                'attackSpeed',
                NumberType::class,
                [
                    'label' => 'label.attackSpeed'
                ]
            )
            ->add(
                'defence',
                NumberType::class,
                [
                    'label' => 'label.defence'
                ]
            )
            ->add(
                'defenceSpeed',
                NumberType::class,
                [
                    'label' => 'label.defenceSpeed'
                ]
            );
    }
}
