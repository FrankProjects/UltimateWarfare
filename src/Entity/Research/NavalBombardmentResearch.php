<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class NavalBombardmentResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Naval Bombardment',
            image: 'research_naval_bombardment.jpg',
            description: 'Unlock the ability to perform naval bombardment operations using cruisers',
            enabled: true,
            costPerLevel: [1 => 60000],
            timestampPerLevel: [1 => 129600],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 4],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'naval-bombardment';
    }
}
