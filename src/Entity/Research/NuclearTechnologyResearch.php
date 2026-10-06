<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class NuclearTechnologyResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Nuclear Technology',
            image: 'research_nuclear_missile_attack.jpg',
            description: 'Unlocks Nuclear Missile production and the Nuclear Missile Attack operation'
                . ' — the ultimate weapon, capable of annihilating an entire enemy region.',
            enabled: true,
            costPerLevel: [1 => 500000000],
            timestampPerLevel: [1 => 604800],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 5],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'nuclear-technology';
    }
}
