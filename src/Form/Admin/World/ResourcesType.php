<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\Admin\World;

use FrankProjects\UltimateWarfare\Entity\World\Resources;
use FrankProjects\UltimateWarfare\Form\Admin\AbstractGameResourcesType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourcesType extends AbstractGameResourcesType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Resources::class,
                'translation_domain' => 'gameresources'
            ]
        );
    }
}
