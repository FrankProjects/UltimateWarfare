<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class DefensiveNetworkResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Defensive Network',
            image: 'research_defensive_network.jpg',
            description: 'A two-layer defense. In ground battles, each level multiplies incoming damage'
                . ' by 0.9 (lvl 5 = enemy deals ~59%). It also reduces the success rate of'
                . ' incoming sniper, bomber, missile, and submarine operations by 6% per level.',
            enabled: true,
            costPerLevel: [
                1 => 10000,
                2 => 50000,
                3 => 250000,
                4 => 1000000,
                5 => 4000000,
            ],
            timestampPerLevel: [
                1 => 3600,
                2 => 7200,
                3 => 15000,
                4 => 45000,
                5 => 100000,
            ],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 1],
                2 => [ResearchTierResearch::class => 1],
                3 => [ResearchTierResearch::class => 1],
                4 => [ResearchTierResearch::class => 1],
                5 => [ResearchTierResearch::class => 1],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'defensive-network';
    }
}
