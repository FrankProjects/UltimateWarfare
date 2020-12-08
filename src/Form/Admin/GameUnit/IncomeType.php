<?php

namespace FrankProjects\UltimateWarfare\Form\Admin\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit\Income;
use FrankProjects\UltimateWarfare\Form\Admin\AbstractGameResourcesType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncomeType extends AbstractGameResourcesType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Income::class,
                'translation_domain' => 'gameresources'
            ]
        );
    }
}
