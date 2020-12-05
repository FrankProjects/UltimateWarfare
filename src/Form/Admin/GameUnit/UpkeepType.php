<?php

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit\Upkeep;
use FrankProjects\UltimateWarfare\Form\Admin\AbstractGameResourcesType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpkeepType extends AbstractGameResourcesType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Upkeep::class,
                'translation_domain' => 'gameresources'
            ]
        );
    }
}
