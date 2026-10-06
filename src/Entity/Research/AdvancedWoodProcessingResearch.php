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
                1 => 100000,
                2 => 200000,
                3 => 400000,
                4 => 800000,
                5 => 1600000,
                6 => 3200000,
                7 => 6400000,
                8 => 12800000,
                9 => 25600000,
                10 => 50000000,
            ],
            timestampPerLevel: [
                1 => 50000,
                2 => 100000,
                3 => 200000,
                4 => 400000,
                5 => 800000,
                6 => 1600000,
                7 => 3200000,
                8 => 6400000,
                9 => 12800000,
                10 => 25600000,
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
