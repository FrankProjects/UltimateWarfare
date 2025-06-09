<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin;

use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStatsType;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\CostType;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\IncomeType;
use FrankProjects\UltimateWarfare\Form\Admin\GameUnit\UpkeepType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;

/** @extends AbstractType<null> */
class GameUnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'label.name',
                    'constraints' => [
                        new Length(min: 4)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'nameMulti',
                TextType::class,
                [
                    'label' => 'label.nameMulti',
                    'constraints' => [
                        new Length(min: 4)
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'netWorth',
                NumberType::class,
                [
                    'label' => 'label.netWorth'
                ]
            )
            ->add(
                'timestamp',
                NumberType::class,
                [
                    'label' => 'label.timestamp'
                ]
            )
            ->add(
                'description',
                TextType::class,
                [
                    'label' => 'label.description'
                ]
            )
            ->add(
                'battleStats',
                BattleStatsType::class,
                [
                    'label' => 'label.battleStats',
                ]
            )
            ->add(
                'cost',
                CostType::class,
                [
                    'label' => 'label.cost',
                ]
            )
            ->add(
                'income',
                IncomeType::class,
                [
                    'label' => 'label.income',
                ]
            )
            ->add(
                'upkeep',
                UpkeepType::class,
                [
                    'label' => 'label.upkeep',
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
                'data_class' => GameUnit::class,
                'translation_domain' => 'gameunit'
            ]
        );
    }
}
