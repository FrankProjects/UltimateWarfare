<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class ResearchTierResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Research Tier',
            image: 'research_tiers.jpg',
            description: 'Advance your research tier to unlock new branches of technology',
            enabled: true,
            costPerLevel: [
                1 => 2500,
                2 => 50000,
                3 => 1000000,
                4 => 25000000,
                5 => 500000000,
            ],
            timestampPerLevel: [
                1 => 180,
                2 => 7200,
                3 => 100000,
                4 => 1000000,
                5 => 8000000,
            ],
        );
    }

    public function getSlug(): string
    {
        return 'research-tier';
    }
}
