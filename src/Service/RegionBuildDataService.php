<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\ConstructionActionService;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorFactory;

/**
 * Builds the data for the world map build and destroy modals of a region: the available
 * categories with their units, the building space left and the region's unit summary.
 */
final class RegionBuildDataService
{
    private ConstructionActionService $constructionActionService;
    private GameUnitRegistry $gameUnitRegistry;
    private GameUnitBehaviorFactory $behaviorFactory;

    public function __construct(
        ConstructionActionService $constructionActionService,
        GameUnitRegistry $gameUnitRegistry,
        GameUnitBehaviorFactory $behaviorFactory
    ) {
        $this->constructionActionService = $constructionActionService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBuildData(WorldRegion $worldRegion, Player $player): array
    {
        $regionType = $worldRegion->getType();
        $waterTypes = ['deep_water', 'water', 'shallow_water', 'sand'];
        $isSandOrWater = in_array($regionType, $waterTypes, true);
        $isSand = $regionType === 'sand';

        // Detect relevant buildings in this region (units + constructions in progress)
        $hasBarrack = false;
        $hasFactory = false;
        $hasAirfield = false;
        $hasHarbor = false;
        $hasMissileFactory = false;

        foreach ($worldRegion->getWorldRegionLeveledUnits() as $worldRegionLeveledUnit) {
            match ($worldRegionLeveledUnit->getGameUnit()) {
                GameUnitEnum::BARRACK => $hasBarrack = true,
                GameUnitEnum::FACTORY => $hasFactory = true,
                GameUnitEnum::AIRFIELD => $hasAirfield = true,
                GameUnitEnum::HARBOR => $hasHarbor = true,
                GameUnitEnum::MISSILE_FACTORY => $hasMissileFactory = true,
                default => null,
            };
        }

        foreach ($worldRegion->getConstructions() as $construction) {
            match ($construction->getGameUnit()) {
                GameUnitEnum::BARRACK => $hasBarrack = true,
                GameUnitEnum::FACTORY => $hasFactory = true,
                GameUnitEnum::AIRFIELD => $hasAirfield = true,
                GameUnitEnum::HARBOR => $hasHarbor = true,
                GameUnitEnum::MISSILE_FACTORY => $hasMissileFactory = true,
                default => null,
            };
        }

        // Determine available categories based on buildings
        $availableCategories = [
            GameUnitCategory::BUILDINGS,
            GameUnitCategory::DEFENSE_BUILDINGS,
            GameUnitCategory::SPECIAL_BUILDINGS,
        ];

        if ($hasBarrack) {
            $availableCategories[] = GameUnitCategory::TROOPS;
        }
        if ($hasAirfield) {
            $availableCategories[] = GameUnitCategory::AIR_UNITS;
        }
        if ($hasHarbor) {
            $availableCategories[] = GameUnitCategory::NAVAL_UNITS;
        }
        if ($hasMissileFactory) {
            $availableCategories[] = GameUnitCategory::MISSILES;
        }

        // Unit counts, derived from the region's collection (also used by getBuildingSpaceLeft) instead of querying
        $gameUnitData = [];
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $key = $worldRegionStackableUnit->getGameUnit()->value;
            $gameUnitData[$key] = ($gameUnitData[$key] ?? 0) + $worldRegionStackableUnit->getAmount();
        }

        // Construction counts and remaining construction time (seconds) per game unit, used to show an ETA countdown.
        // Derived from the constructions collection already loaded above instead of querying again.
        $constructionData = [];
        $constructionTimeLeft = [];
        foreach ($worldRegion->getConstructions() as $construction) {
            $key = $construction->getGameUnit()->value;
            $left = max(0, ($construction->getTimestamp() + $construction->getDuration()) - time());
            $constructionData[$key] = ($constructionData[$key] ?? 0) + $construction->getNumber();
            $constructionTimeLeft[$key] = max($constructionTimeLeft[$key] ?? 0, $left);
        }
        $spaceLeft = $this->constructionActionService->getBuildingSpaceLeft(GameUnitCategory::BUILDINGS, $worldRegion);

        // Build player's completed research slugs for research gating
        $completedResearchSlugs = [];
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() === true) {
                $completedResearchSlugs[] = $playerResearch->getResearchSlug();
            }
        }

        $categories = [];
        foreach ($availableCategories as $gameUnitCategory) {
            $gameUnits = $this->gameUnitRegistry->findByCategory($gameUnitCategory);
            $units = [];

            foreach ($gameUnits as $gameUnit) {
                $rowName = $gameUnit->getRowName();

                // Filter harbor from special buildings when region is not sand
                if ($gameUnitCategory === GameUnitCategory::SPECIAL_BUILDINGS && $rowName === 'harbor' && !$isSand) {
                    continue;
                }

                // Filter sea mines from defense buildings when region is not sand or water
                if (
                    $gameUnitCategory === GameUnitCategory::DEFENSE_BUILDINGS
                    && $rowName === 'sea_mine'
                    && !$isSandOrWater
                ) {
                    continue;
                }

                $behavior = $this->behaviorFactory->create($gameUnit);
                $canBuild = $behavior->canBuild($worldRegion, $player);
                $buildRequirement = '';

                // Filter tanks from troops when no factory
                if ($gameUnitCategory === GameUnitCategory::TROOPS && $rowName === 'tank' && !$hasFactory) {
                    $canBuild = false;
                    $buildRequirement = 'Requires a Factory';
                }

                // Check research gating
                $researchSlug = $gameUnit->getResearchSlug();
                $hasRequiredResearch = $researchSlug === null
                    || in_array($researchSlug, $completedResearchSlugs, true);

                if (!$hasRequiredResearch) {
                    $canBuild = false;
                    $buildRequirement = 'Requires ' . $gameUnit->getResearchName() . ' research';
                } elseif (!$canBuild && $buildRequirement === '') {
                    $buildRequirement = $behavior->getBuildRequirementDescription();
                }

                $leveledUnit = $gameUnitCategory->isLeveled()
                    ? $worldRegion->getLeveledUnit($gameUnit->getGameUnitEnum())
                    : null;
                $currentHealth = $leveledUnit?->getHealth() ?? 0;
                $maxHealth = $leveledUnit !== null
                    ? $gameUnit->getBattleStats()->getHealth() * $leveledUnit->getLevel()
                    : 0;

                $units[] = [
                    'gameUnitEnum' => $gameUnit->getGameUnitEnum(),
                    'name' => $gameUnit->getName(),
                    'description' => $gameUnit->getDescription(),
                    'image' => $gameUnit->getImage(),
                    'imageDir' => $gameUnitCategory->getImageDir(),
                    'costCash' => $gameUnit->getCost()->getCash(),
                    'costWood' => $gameUnit->getCost()->getWood(),
                    'costSteel' => $gameUnit->getCost()->getSteel(),
                    'costFood' => $gameUnit->getCost()->getFood(),
                    'incomeCash' => $gameUnit->getIncome()->getCash(),
                    'incomeWood' => $gameUnit->getIncome()->getWood(),
                    'incomeSteel' => $gameUnit->getIncome()->getSteel(),
                    'incomeFood' => $gameUnit->getIncome()->getFood(),
                    'upkeepCash' => $gameUnit->getUpkeep()->getCash(),
                    'upkeepWood' => $gameUnit->getUpkeep()->getWood(),
                    'upkeepSteel' => $gameUnit->getUpkeep()->getSteel(),
                    'upkeepFood' => $gameUnit->getUpkeep()->getFood(),
                    'netWorth' => $gameUnit->getNetWorth(),
                    'timestamp' => $gameUnit->getTimestamp(),
                    'canBuild' => $canBuild,
                    'buildRequirement' => $buildRequirement,
                    'owned' => $gameUnitData[$gameUnit->getGameUnitEnum()->value] ?? 0,
                    'inConstruction' => $constructionData[$gameUnit->getGameUnitEnum()->value] ?? 0,
                    'constructionTimeLeft' => $constructionTimeLeft[$gameUnit->getGameUnitEnum()->value] ?? 0,
                    'isLeveled' => $gameUnitCategory->isLeveled(),
                    'level' => $leveledUnit?->getLevel() ?? 0,
                    'maxLevel' => 10,
                    'health' => $currentHealth,
                    'maxHealth' => $maxHealth,
                ];
            }

            $categories[] = [
                'id' => $gameUnitCategory->value,
                'name' => $gameUnitCategory->getLabel(),
                'units' => $units,
            ];
        }

        return [
            'regionId' => $worldRegion->getId(),
            'regionType' => $regionType,
            'spaceLeft' => $spaceLeft,
            'categories' => $categories,
            'units' => $this->gameUnitRegistry->getRegionUnitSummary($worldRegion),
        ];
    }
}
