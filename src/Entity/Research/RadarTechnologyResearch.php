<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class RadarTechnologyResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Radar Technology',
            image: 'research_radar_technology.jpg',
            description: "Unlock the Radar Station building, which detects enemy troop movements"
                . " in the region it's built in.",
            enabled: true,
            costPerLevel: [1 => 40000],
            timestampPerLevel: [1 => 43200],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 2],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'radar-technology';
    }
}
