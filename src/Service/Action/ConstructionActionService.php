<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorFactory;
use FrankProjects\UltimateWarfare\Service\NetWorthUpdaterService;
use RuntimeException;

final class ConstructionActionService
{
    private ConstructionRepository $constructionRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private PlayerRepository $playerRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private GameUnitBehaviorFactory $behaviorFactory;

    public function __construct(
        ConstructionRepository $constructionRepository,
        GameUnitRegistry $gameUnitRegistry,
        PlayerRepository $playerRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        NetWorthUpdaterService $netWorthUpdaterService,
        GameUnitBehaviorFactory $behaviorFactory
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->playerRepository = $playerRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * @param array<int, string> $constructionData
     */
    public function constructGameUnits(
        WorldRegion $region,
        Player $player,
        GameUnitCategory $gameUnitCategory,
        array $constructionData
    ): void {
        if ($gameUnitCategory->isLeveled()) {
            throw new RuntimeException(
                'Leveled buildings must be built through the dedicated building endpoints.'
            );
        }

        $this->validateCategoryAllowed($region, $gameUnitCategory);

        $priceCash = 0;
        $priceWood = 0;
        $priceSteel = 0;
        $totalBuild = 0;
        /** @var list<array{gameUnit: GameUnit, amount: int, duration: int}> $queue */
        $queue = [];

        foreach ($constructionData as $gameUnitId => $amount) {
            $amount = intval($amount);
            if ($amount < 1) {
                continue;
            }

            $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitId);
            if ($gameUnitEnum === null) {
                continue;
            }

            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            if ($gameUnit->getGameUnitCategory() !== $gameUnitCategory) {
                continue;
            }

            $this->validateUnitAllowed($gameUnit, $region);

            $behavior = $this->behaviorFactory->create($gameUnit);
            if (!$behavior->canBuild($region, $player)) {
                throw new RuntimeException(
                    "Cannot build {$gameUnit->getName()}: " .
                    $behavior->getBuildRequirementDescription()
                );
            }

            $researchSlug = $gameUnit->getResearchSlug();
            if ($researchSlug !== null && !$this->playerHasResearch($player, $researchSlug)) {
                throw new RuntimeException(
                    "Cannot build {$gameUnit->getName()}: requires {$gameUnit->getResearchName()} research"
                );
            }

            $costMultiplier = $amount;
            $duration = $this->calculateBuildDuration($gameUnit, $region);

            $priceCash = $priceCash + ($costMultiplier * $gameUnit->getCost()->getCash());
            $priceWood = $priceWood + ($costMultiplier * $gameUnit->getCost()->getWood());
            $priceSteel = $priceSteel + ($costMultiplier * $gameUnit->getCost()->getSteel());

            if ($gameUnitCategory === GameUnitCategory::BUILDINGS) {
                $totalBuild = $totalBuild + $amount;
            }

            $queue[] = ['gameUnit' => $gameUnit, 'amount' => $amount, 'duration' => $duration];
        }

        if ($gameUnitCategory === GameUnitCategory::BUILDINGS) {
            $buildingsInConstruction = $this->getCountGameUnitsInConstruction($region, $gameUnitCategory);
            $regionBuildings = $this->getCountGameUnitsInWorldRegion($region, $gameUnitCategory);
            $totalSpace = $this->getEffectiveSpace($region, $player) - $regionBuildings - $buildingsInConstruction;

            if ($totalBuild > $totalSpace) {
                throw new RuntimeException('You do not have that much building space.');
            }
        }

        $resources = $player->getResources();

        if ($priceCash > $resources->getCash()) {
            throw new RuntimeException("You don't have enough cash to build that.");
        }
        if ($priceWood > $resources->getWood()) {
            throw new RuntimeException("You don't have enough wood to build that.");
        }
        if ($priceSteel > $resources->getSteel()) {
            throw new RuntimeException("You don't have enough steel to build that.");
        }

        if (count($queue) === 0) {
            throw new RuntimeException("You didn't select anything to build.");
        }

        $resources->setCash($resources->getCash() - $priceCash);
        $resources->setWood($resources->getWood() - $priceWood);
        $resources->setSteel($resources->getSteel() - $priceSteel);

        $player->setResources($resources);
        $this->playerRepository->save($player);

        // Constructions are only created once validation passed, as creating one adds it to the region
        foreach ($queue as $item) {
            $construction = Construction::create(
                $region,
                $player,
                $item['gameUnit']->getGameUnitEnum(),
                $item['amount'],
                $item['duration']
            );
            $this->constructionRepository->save($construction);

            $this->behaviorFactory->create($item['gameUnit'])->onBuild($region, $item['amount']);
        }
    }

    /**
     * Validate that the given game unit category is allowed to be built on this region.
     */
    private function validateCategoryAllowed(WorldRegion $region, GameUnitCategory $gameUnitCategory): void
    {
        match ($gameUnitCategory) {
            GameUnitCategory::TROOPS => $this->requireLeveledBuilding($region, GameUnitEnum::BARRACK, 'a Barrack'),
            GameUnitCategory::AIR_UNITS => $this->requireLeveledBuilding(
                $region,
                GameUnitEnum::AIRFIELD,
                'an Airfield'
            ),
            GameUnitCategory::NAVAL_UNITS => $this->requireLeveledBuilding($region, GameUnitEnum::HARBOR, 'a Harbor'),
            GameUnitCategory::MISSILES => $this->requireLeveledBuilding(
                $region,
                GameUnitEnum::MISSILE_FACTORY,
                'a Missile Factory'
            ),
            GameUnitCategory::BUILDINGS,
            GameUnitCategory::DEFENSE_BUILDINGS,
            GameUnitCategory::SPECIAL_BUILDINGS => null, // Always allowed at category level
        };
    }

    /**
     * Validate that a specific game unit is allowed to be built on this region.
     */
    private function validateUnitAllowed(GameUnit $gameUnit, WorldRegion $region): void
    {
        $rowName = $gameUnit->getRowName();
        $regionType = $region->getType();

        // Tanks require a Factory on this region
        if ($rowName === 'tank') {
            $this->requireLeveledBuilding($region, GameUnitEnum::FACTORY, 'a Factory');
        }

        // Harbor can only be built on sand regions
        if ($rowName === 'harbor' && $regionType !== WorldRegion::TYPE_SAND) {
            throw new RuntimeException("Cannot build {$gameUnit->getName()}: requires a sand region.");
        }

        // Sea mines can only be built on coastal or water regions
        $waterTypes = [
            WorldRegion::TYPE_DEEP_WATER, WorldRegion::TYPE_WATER,
            WorldRegion::TYPE_SHALLOW_WATER, WorldRegion::TYPE_SAND,
        ];
        if ($rowName === 'sea_mine' && !in_array($regionType, $waterTypes, true)) {
            throw new RuntimeException("Cannot build {$gameUnit->getName()}: requires a sand or water region.");
        }
    }

    /**
     * Check that a leveled building (Defense / Special) of the given type is built on the region.
     */
    private function requireLeveledBuilding(WorldRegion $region, GameUnitEnum $gameUnit, string $readableName): void
    {
        if ($region->getLeveledUnit($gameUnit) === null) {
            throw new RuntimeException("This region requires $readableName before you can build this.");
        }
    }

    /**
     * Apply the build-speed bonus of the relevant enabling Special building (5% per level
     * above level 1, capped at level 10) to a unit's base build time.
     */
    private function calculateBuildDuration(GameUnit $gameUnit, WorldRegion $region): int
    {
        $base = $gameUnit->getTimestamp();
        $enabling = $this->getEnablingBuildingEnum($gameUnit);
        if ($enabling === null) {
            return $base;
        }

        $level = $region->getUnitLevel($enabling);
        if ($level < 2) {
            return $base;
        }

        $factor = 0.05 * ($level - 1);

        return max(1, (int) round($base * (1.0 - $factor)));
    }

    /**
     * The Special building whose level speeds up construction of the given unit, if any.
     * Tanks and artillery are sped up by the Factory; other category units by their gate.
     */
    private function getEnablingBuildingEnum(GameUnit $gameUnit): ?GameUnitEnum
    {
        if (in_array($gameUnit->getRowName(), ['tank', 'artillery'], true)) {
            return GameUnitEnum::FACTORY;
        }

        return match ($gameUnit->getGameUnitCategory()) {
            GameUnitCategory::TROOPS => GameUnitEnum::BARRACK,
            GameUnitCategory::AIR_UNITS => GameUnitEnum::AIRFIELD,
            GameUnitCategory::NAVAL_UNITS => GameUnitEnum::HARBOR,
            GameUnitCategory::MISSILES => GameUnitEnum::MISSILE_FACTORY,
            default => null,
        };
    }

    /**
     * @param array<int, string> $destroyData
     */
    public function removeGameUnits(
        WorldRegion $region,
        Player $player,
        GameUnitCategory $gameUnitCategory,
        array $destroyData
    ): void {
        if ($gameUnitCategory->isLeveled()) {
            throw new RuntimeException('Leveled buildings cannot be destroyed.');
        }

        $isRemoving = false;
        foreach ($destroyData as $gameUnitId => $amount) {
            $amount = intval($amount);
            if ($amount < 1) {
                continue;
            }

            $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitId);
            if ($gameUnitEnum === null) {
                continue;
            }

            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            if ($gameUnit->getGameUnitCategory() !== $gameUnitCategory) {
                continue;
            }

            $this->removeGameUnitsFromWorldRegion($region, $gameUnit, $amount);
            $isRemoving = true;
        }

        if ($isRemoving === true) {
            $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
        } else {
            throw new RuntimeException("You didn't select anything to remove.");
        }
    }

    /**
     * @return WorldRegion The region the cancelled construction belonged to
     */
    public function cancelConstruction(Player $player, int $constructionId): WorldRegion
    {
        $construction = $this->constructionRepository->find($constructionId);

        if ($construction === null) {
            throw new RuntimeException('This construction queue does not exist!');
        }

        if ($construction->getPlayer()->getId() !== $player->getId()) {
            throw new RuntimeException('This is not your construction queue!');
        }

        $this->constructionRepository->remove($construction);

        return $construction->getWorldRegion();
    }

    public function getBuildingSpaceLeft(GameUnitCategory $gameUnitCategory, WorldRegion $worldRegion): int
    {
        if ($gameUnitCategory !== GameUnitCategory::BUILDINGS) {
            return 0;
        }

        $buildingsInConstruction = $this->getCountGameUnitsInConstruction($worldRegion, $gameUnitCategory);
        $regionBuildings = $this->getCountGameUnitsInWorldRegion($worldRegion, $gameUnitCategory);

        return $this->getEffectiveSpace($worldRegion, $worldRegion->getPlayer())
            - $regionBuildings - $buildingsInConstruction;
    }

    private function getEffectiveSpace(WorldRegion $worldRegion, ?Player $player): int
    {
        if ($player === null) {
            return $worldRegion->getSpace();
        }

        $level = 0;
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() !== true) {
                continue;
            }
            if ($playerResearch->getResearchSlug() !== 'efficient-building-technology') {
                continue;
            }
            if ($playerResearch->getLevel() > $level) {
                $level = $playerResearch->getLevel();
            }
        }

        if ($level === 0) {
            return $worldRegion->getSpace();
        }

        return (int) ($worldRegion->getSpace() * (1.0 + 0.10 * $level));
    }

    public function getCountGameUnitsInConstruction(WorldRegion $worldRegion, GameUnitCategory $gameUnitCategory): int
    {
        return $this->constructionRepository->getGameUnitConstructionSumByWorldRegionAndCategory(
            $worldRegion,
            $gameUnitCategory
        );
    }

    public function getCountGameUnitsInWorldRegion(WorldRegion $worldRegion, GameUnitCategory $gameUnitCategory): int
    {
        $regionBuildings = 0;
        foreach ($worldRegion->getWorldRegionStackableUnits() as $regionUnit) {
            $gameUnit = $this->gameUnitRegistry->find($regionUnit->getGameUnit());
            if ($gameUnit->getGameUnitCategory() === $gameUnitCategory) {
                $regionBuildings += $regionUnit->getAmount();
            }
        }

        return $regionBuildings;
    }

    private function playerHasResearch(Player $player, string $researchSlug): bool
    {
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() === true && $playerResearch->getResearchSlug() === $researchSlug) {
                return true;
            }
        }

        return false;
    }

    private function removeGameUnitsFromWorldRegion(WorldRegion $worldRegion, GameUnit $gameUnit, int $amount): void
    {
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() !== $gameUnit->getGameUnitEnum()) {
                continue;
            }

            if ($amount > $worldRegionStackableUnit->getAmount()) {
                throw new RuntimeException('You do not have that many ' . $gameUnit->getName() . "s!");
            }

            $worldRegionStackableUnit->setAmount($worldRegionStackableUnit->getAmount() - $amount);
            $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
        }
    }
}
