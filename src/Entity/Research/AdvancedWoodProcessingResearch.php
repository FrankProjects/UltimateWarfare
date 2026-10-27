<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class AdvancedWoodProcessingResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Advanced Wood Processing',
            image: 'research_advanced_wood_processing.jpg',
            description: 'Increase wood income by 10% per level (lvl 10 = +100%).',
            enabled: true,
            costPerLevel: [
                1 => 30000,
                2 => 60000,
                3 => 120000,
                4 => 250000,
                5 => 500000,
                6 => 1000000,
                7 => 2000000,
                8 => 4000000,
                9 => 8000000,
                10 => 15000000,
            ],
            timestampPerLevel: [
                1 => 7200,
                2 => 60000,
                3 => 120000,
                4 => 180000,
                5 => 240000,
                6 => 300000,
                7 => 360000,
                8 => 420000,
                9 => 480000,
                10 => 540000,
            ],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 3],
                2 => [ResearchTierResearch::class => 3],
                3 => [ResearchTierResearch::class => 3],
                4 => [ResearchTierResearch::class => 3],
                5 => [ResearchTierResearch::class => 3],
                6 => [ResearchTierResearch::class => 3],
                7 => [ResearchTierResearch::class => 3],
                8 => [ResearchTierResearch::class => 3],
                9 => [ResearchTierResearch::class => 3],
                10 => [ResearchTierResearch::class => 3],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'advanced-wood-processing';
    }
}
