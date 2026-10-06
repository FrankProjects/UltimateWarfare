<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class SpyTechnologyResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Spy Technology',
            image: 'research_spy_technology.jpg',
            description: 'Spy technology can be used to spy on enemy countries and retrieve detailed data.'
                . ' Level 1 unlocks the Spy Operation, level 2 unlocks the Advanced Spy Operation,'
                . ' and each additional level (3-10) further increases the success rate of all spy operations.',
            enabled: true,
            costPerLevel: [
                1 => 20000,
                2 => 500000,
                3 => 1500000,
                4 => 3000000,
                5 => 5000000,
                6 => 8000000,
                7 => 12000000,
                8 => 17000000,
                9 => 23000000,
                10 => 30000000,
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
                1 => [ResearchTierResearch::class => 2],
                2 => [ResearchTierResearch::class => 2],
                3 => [ResearchTierResearch::class => 2],
                4 => [ResearchTierResearch::class => 2],
                5 => [ResearchTierResearch::class => 2],
                6 => [ResearchTierResearch::class => 2],
                7 => [ResearchTierResearch::class => 2],
                8 => [ResearchTierResearch::class => 2],
                9 => [ResearchTierResearch::class => 2],
                10 => [ResearchTierResearch::class => 2],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'spy-technology';
    }
}
