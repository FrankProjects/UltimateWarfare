<?php

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit\Cost;
use FrankProjects\UltimateWarfare\Form\Admin\AbstractGameResourcesType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CostType extends AbstractGameResourcesType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cost::class,
            'translation_domain' => 'gameresources'
        ]);
    }
}
