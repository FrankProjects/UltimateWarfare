<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class AdvancedOpticsResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Advanced Optics',
            image: 'research_advanced_optics.jpg',
            description: 'Unlock the ability to train snipers and perform sniper attack operations',
            enabled: true,
            costPerLevel: [1 => 35000],
            timestampPerLevel: [1 => 86400],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 2],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'advanced-optics';
    }
}
