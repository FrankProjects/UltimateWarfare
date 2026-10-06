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

        // Check 2: Must be adjacent to water
        return $this->isAdjacentToWater($region);
    }

    public function getBuildRequirementDescription(): string
    {
        return 'Requires a Harbor and must be adjacent to water';
    }

    private function isAdjacentToWater(WorldRegion $region): bool
    {
        $adjacentRegions = $this->regionRepository->findAdjacentRegions(
            $region->getX(),
            $region->getY(),
            $region->getWorld()
        );

        $waterTypes = [
            WorldRegion::TYPE_DEEP_WATER,
            WorldRegion::TYPE_WATER,
            WorldRegion::TYPE_SHALLOW_WATER,
        ];

        foreach ($adjacentRegions as $adjacent) {
            if (in_array($adjacent->getType(), $waterTypes, true)) {
                return true;
            }
        }

        return false;
    }

    public function canMoveTo(WorldRegion $from, WorldRegion $to): bool
    {
        // Naval units can only move to water or coastal regions
        $navalTypes = [
            WorldRegion::TYPE_DEEP_WATER, WorldRegion::TYPE_WATER,
            WorldRegion::TYPE_SHALLOW_WATER, WorldRegion::TYPE_SAND,
        ];
        return in_array($to->getType(), $navalTypes, true);
    }
}
