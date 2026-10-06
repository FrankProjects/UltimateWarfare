<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class BomberAttackResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Special Operation: Bomber Attack',
            image: 'research_bomber_attack.jpg',
            description: 'Unlock the Bomber Attack operation — destroy enemy special buildings'
                . ' (train stations, airfields, harbors) with bomber raids.',
            enabled: true,
            costPerLevel: [1 => 100000],
            timestampPerLevel: [1 => 15000],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 3],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'special-operation-bomber-attack';
    }
}
