<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\SpyTechnologyResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\Spy;

final readonly class SpyOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Spy Operation',
            image: 'op_spy.jpg',
            cost: 5000,
            description: "Send a spy team into an enemy region to reveal their full unit, building,"
                . " and defense layout. On failure, the enemy is alerted.",
            enabled: true,
            difficulty: 0.1,
            maxDistance: 3,
            researchClass: SpyTechnologyResearch::class,
            gameUnit: null,
        );
    }

    public function getSlug(): string
    {
        return 'spy';
    }

    public function getProcessorClass(): string
    {
        return Spy::class;
    }
}
