<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Research;

use FrankProjects\UltimateWarfare\Entity\Research;

final readonly class ArtilleryBombardmentResearch extends Research
{
    public function __construct()
    {
        parent::__construct(
            name: 'Special Operation: Artillery Bombardment',
            image: 'research_artillery_bombardment.jpg',
            description: 'Unlock the Artillery Bombardment operation — bombard adjacent enemy regions'
                . ' with your artillery units.',
            enabled: true,
            costPerLevel: [1 => 25000],
            timestampPerLevel: [1 => 7200],
            prerequisitesPerLevel: [
                1 => [ResearchTierResearch::class => 2],
            ],
        );
    }

    public function getSlug(): string
    {
        return 'special-operation-artillery-bombardment';
    }
}
