<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class CounterEspionageResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Counter Espionage',
            image: 'research_counter_espionage.jpg',
            description: 'Train counter-intelligence to defend against enemy spy operations.'
                . ' Each level reduces the success rate of incoming Spy and Advanced Spy operations by 10%.',
            enabled: true,
            costPerLevel: [
                1 => 50000,
                2 => 250000,
                3 => 1000000,
                4 => 3000000,
                5 => 8000000,
            ],
            timestampPerLevel: [
                1 => 7200,
                2 => 60000,
                3 => 180000,
                4 => 300000,
                5 => 432000,
            ],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 3],
                2 => [ResearchTierResearch::class => 3],
                3 => [ResearchTierResearch::class => 3],
                4 => [ResearchTierResearch::class => 3],
                5 => [ResearchTierResearch::class => 3],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'counter-espionage';
    }
}
