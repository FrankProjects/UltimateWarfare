<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class BallisticMissileTechnologyResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Ballistic Missile Technology',
            image: 'research_ballistic_missile_technology.jpg',
            description: 'Unlock the ability to construct missile factories, build rockets,'
                . ' and perform missile attack operations.',
            enabled: true,
            costPerLevel: [1 => 75000],
            timestampPerLevel: [1 => 172800],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 4],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'ballistic-missile-technology';
    }
}
