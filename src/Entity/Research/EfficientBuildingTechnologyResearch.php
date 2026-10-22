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
                6 => 12000000,
                7 => 18000000,
                8 => 25000000,
                9 => 35000000,
                10 => 50000000,
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
