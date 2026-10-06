<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameUnit\Behavior;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Service\GameUnit\AbstractGameUnitBehavior;

class DefaultUnitBehavior extends AbstractGameUnitBehavior
{
    public function canBuild(WorldRegion $region, Player $player): bool
    {
        return true; // No special requirements
    }

    public function getBuildRequirementDescription(): string
    {
        return 'No special requirements';
    }
}
