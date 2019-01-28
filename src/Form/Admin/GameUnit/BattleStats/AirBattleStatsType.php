<?php

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit\BattleStats;

use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\AirBattleStats;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirBattleStatsType extends AbstractBattleStatsType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AirBattleStats::class,
            'translation_domain' => 'gameunit'
        ]);
    }
}
