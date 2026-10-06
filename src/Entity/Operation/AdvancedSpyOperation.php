<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\SpyTechnologyResearch;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\AdvancedSpy;

final readonly class AdvancedSpyOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Advanced Spy Operation',
            image: 'op_advanced_spy.jpg',
            cost: 10000,
            description: "Run a deep intelligence operation against an enemy. Reveals their empire-wide"
                . " cash, food, wood, steel, and population, plus every report they filed in the last 24 hours."
                . " Cost scales with how many regions the target controls. On failure, the enemy is alerted.",
            enabled: true,
            difficulty: 0.3,
            maxDistance: 6,
            researchClass: SpyTechnologyResearch::class,
            gameUnit: null,
            researchMinLevel: 2,
        );
    }

    public function getSlug(): string
    {
        return 'advanced-spy';
    }

    public function getProcessorClass(): string
    {
        return AdvancedSpy::class;
    }

    public function getCostType(): string
    {
        return self::COST_PER_TARGET_REGION;
    }

    public function calculateCost(WorldRegion $targetRegion, int $amount): int
    {
        $targetPlayer = $targetRegion->getPlayer();
        $regionCount = $targetPlayer === null ? 1 : max(1, count($targetPlayer->getWorldRegions()));

        return $this->getCost() * $regionCount;
    }
}
