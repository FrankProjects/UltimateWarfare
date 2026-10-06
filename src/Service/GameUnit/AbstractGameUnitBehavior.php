<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameUnit;

use FrankProjects\UltimateWarfare\Entity\WorldRegion;

abstract class AbstractGameUnitBehavior implements GameUnitBehaviorInterface
{
    public function onBuild(WorldRegion $region, int $amount): void
    {
        // Default: no special behavior
    }

    public function onDestroy(WorldRegion $region, int $amount): void
    {
        // Default: no special behavior
    }

    public function canMoveTo(WorldRegion $from, WorldRegion $to): bool
    {
        return true; // Default: can move anywhere
    }
}
