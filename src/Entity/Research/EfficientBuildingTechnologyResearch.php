<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class EfficientBuildingTechnologyResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Efficient Building Technology',
            image: 'research_efficient_building_technology.jpg',
            description: "Increase each region's building capacity by 10% per level"
                . ' (lvl 10 = +100% buildable space).',
            enabled: true,
            costPerLevel: [
                1 => 500000,
                2 => 1000000,
                3 => 2000000,
                4 => 4000000,
                5 => 8000000,
                6 => 16000000,
                7 => 32000000,
                8 => 64000000,
                9 => 128000000,
                10 => 256000000,
            ],
            timestampPerLevel: [
                1 => 200000,
                2 => 400000,
                3 => 800000,
                4 => 1600000,
                5 => 3200000,
                6 => 6400000,
                7 => 12800000,
                8 => 25600000,
                9 => 51200000,
                10 => 102400000,
            ],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 4],
                2 => [ResearchTierResearch::class => 4],
                3 => [ResearchTierResearch::class => 4],
                4 => [ResearchTierResearch::class => 4],
                5 => [ResearchTierResearch::class => 4],
                6 => [ResearchTierResearch::class => 4],
                7 => [ResearchTierResearch::class => 4],
                8 => [ResearchTierResearch::class => 4],
                9 => [ResearchTierResearch::class => 4],
                10 => [ResearchTierResearch::class => 4],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'efficient-building-technology';
    }
}
