<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameUnit\Behavior;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\GameUnit\AbstractGameUnitBehavior;

class NavalUnitBehavior extends AbstractGameUnitBehavior
{
    public function __construct(
        private WorldRegionRepository $regionRepository
    ) {
    }

    public function canBuild(WorldRegion $region, Player $player): bool
    {
        // Check 1: Must have Harbor
        if ($region->getLeveledUnit(GameUnitEnum::HARBOR) === null) {
            return false;
        }

        // Check 2: Must be coastal
        return $this->isCoastal($region);
    }

    public function getBuildRequirementDescription(): string
    {
        return 'Requires a Harbor on a coastal region';
    }

    private function isCoastal(WorldRegion $region): bool
    {
        return $region->isCoastal($this->regionRepository->findAdjacentRegions(
            $region->getX(),
            $region->getY(),
            $region->getWorld()
        ));
    }

    public function canMoveTo(WorldRegion $from, WorldRegion $to): bool
    {
        // Naval units can only move to water or coastal regions
        return $to->isWater() || $this->isCoastal($to);
    }
}
