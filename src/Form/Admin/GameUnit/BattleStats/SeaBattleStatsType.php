<?php

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats;

use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\SeaBattleStats;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeaBattleStatsType extends AbstractBattleStatsType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SeaBattleStats::class,
            'translation_domain' => 'gameunit'
        ]);
    }
}
