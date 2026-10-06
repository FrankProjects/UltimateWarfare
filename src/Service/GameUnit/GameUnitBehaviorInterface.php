<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameUnit;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface GameUnitBehaviorInterface
{
    /**
     * Check if this unit can be built in the given region
     */
    public function canBuild(WorldRegion $region, Player $player): bool;

    /**
     * Get human-readable reason why unit cannot be built
     */
    public function getBuildRequirementDescription(): string;

    /**
     * Called when unit is built (for side effects)
     */
    public function onBuild(WorldRegion $region, int $amount): void;

    /**
     * Called when unit is destroyed
     */
    public function onDestroy(WorldRegion $region, int $amount): void;

    /**
     * Check if unit can move to target region
     */
    public function canMoveTo(WorldRegion $from, WorldRegion $to): bool;
}
