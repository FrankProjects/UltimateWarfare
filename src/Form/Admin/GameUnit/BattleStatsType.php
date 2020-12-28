<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats\AirBattleStatsType;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats\GroundBattleStatsType;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats\SeaBattleStatsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BattleStatsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'health',
                TextType::class,
                [
                    'label' => 'label.health'
                ]
            )
            ->add(
                'armor',
                TextType::class,
                [
                    'label' => 'label.armor'
                ]
            )
            ->add(
                'travelSpeed',
                TextType::class,
                [
                    'label' => 'label.travelSpeed'
                ]
            )
            ->add(
                'airBattleStats',
                AirBattleStatsType::class,
                [
                    'label' => 'label.airBattleStats'
                ]
            )
            ->add(
                'seaBattleStats',
                SeaBattleStatsType::class,
                [
                    'label' => 'label.seaBattleStats'
                ]
            )
            ->add(
                'groundBattleStats',
                GroundBattleStatsType::class,
                [
                    'label' => 'label.groundBattleStats'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => BattleStats::class,
                'translation_domain' => 'gameunit'
            ]
        );
    }
}
