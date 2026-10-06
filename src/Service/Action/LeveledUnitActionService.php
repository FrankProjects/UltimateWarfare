<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Service\GameUnit\GameUnitBehaviorFactory;
use RuntimeException;

/**
 * Handles the leveled-building lifecycle (Defense / Special buildings): build, upgrade
 * and repair. Fully isolated from the stackable construction path in
 * ConstructionActionService. Building and upgrading are timed and go through the shared
 * Construction queue; repair is instant.
 */
final class LeveledUnitActionService
{
    private const int MAX_BUILDING_LEVEL = 10;

    private ConstructionRepository $constructionRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private PlayerRepository $playerRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;
    private GameUnitBehaviorFactory $behaviorFactory;

    public function __construct(
        ConstructionRepository $constructionRepository,
        GameUnitRegistry $gameUnitRegistry,
        PlayerRepository $playerRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        GameUnitBehaviorFactory $behaviorFactory
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->playerRepository = $playerRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * Queue construction of a new leveled building (level 1) on the region.
     */
    public function build(WorldRegion $region, Player $player, GameUnitEnum $gameUnitEnum): void
    {
        $gameUnit = $this->resolveLeveledGameUnit($gameUnitEnum);

        if ($region->getLeveledUnit($gameUnitEnum) !== null) {
            throw new RuntimeException("{$gameUnit->getName()} is already built on this region.");
        }
        if ($this->getQueuedCount($region, $gameUnitEnum) > 0) {
            throw new RuntimeException("{$gameUnit->getName()} is already being built on this region.");
        }

        $this->validateBuildable($gameUnit, $region, $player);

        $this->chargeResources($player, $gameUnit, 1);
        $this->queueConstruction($region, $player, $gameUnit);
    }

    /**
     * Queue an upgrade (one level) of an existing leveled building on the region.
     */
    public function upgrade(WorldRegion $region, Player $player, GameUnitEnum $gameUnitEnum): void
    {
        $gameUnit = $this->resolveLeveledGameUnit($gameUnitEnum);

        if ($region->getLeveledUnit($gameUnitEnum) === null) {
            throw new RuntimeException("{$gameUnit->getName()} is not built on this region yet.");
        }

        $this->validateBuildable($gameUnit, $region, $player);

        $targetLevel = $region->getUnitLevel($gameUnitEnum) + $this->getQueuedCount($region, $gameUnitEnum) + 1;
        if ($targetLevel > self::MAX_BUILDING_LEVEL) {
            throw new RuntimeException("{$gameUnit->getName()} is already at the maximum level.");
        }

        // Escalating cost: each level costs the base cost multiplied by the target level.
        $this->chargeResources($player, $gameUnit, $targetLevel);
        $this->queueConstruction($region, $player, $gameUnit);
    }

    /**
     * Instantly restore a damaged leveled building to full health. The cost scales with
     * the missing health and the building's level.
     */
    public function repair(WorldRegion $region, Player $player, GameUnitEnum $gameUnitEnum): void
    {
        $gameUnit = $this->resolveLeveledGameUnit($gameUnitEnum);

        $leveledUnit = $region->getLeveledUnit($gameUnitEnum);
        if ($leveledUnit === null) {
            throw new RuntimeException("{$gameUnit->getName()} is not built on this region.");
        }

        $maxHealth = $gameUnit->getBattleStats()->getHealth() * $leveledUnit->getLevel();
        $missingHealth = $maxHealth - $leveledUnit->getHealth();
        if ($missingHealth <= 0) {
            throw new RuntimeException("{$gameUnit->getName()} is already at full health.");
        }

        $fraction = $missingHealth / $maxHealth;
        $costCash = (int) ceil($gameUnit->getCost()->getCash() * $leveledUnit->getLevel() * $fraction);
        $costWood = (int) ceil($gameUnit->getCost()->getWood() * $leveledUnit->getLevel() * $fraction);
        $costSteel = (int) ceil($gameUnit->getCost()->getSteel() * $leveledUnit->getLevel() * $fraction);

        $this->deductResources($player, $costCash, $costWood, $costSteel);

        $leveledUnit->setHealth($maxHealth);
        $this->worldRegionLeveledUnitRepository->save($leveledUnit);
    }

    private function resolveLeveledGameUnit(GameUnitEnum $gameUnitEnum): GameUnit
    {
        $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
        if (!$gameUnit->getGameUnitCategory()->isLeveled()) {
            throw new RuntimeException("{$gameUnit->getName()} is not a leveled building.");
        }

        return $gameUnit;
    }

    private function validateBuildable(GameUnit $gameUnit, WorldRegion $region, Player $player): void
    {
        $rowName = $gameUnit->getRowName();
        $regionType = $region->getType();

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

        $behavior = $this->behaviorFactory->create($gameUnit);
        if (!$behavior->canBuild($region, $player)) {
            throw new RuntimeException(
                "Cannot build {$gameUnit->getName()}: " . $behavior->getBuildRequirementDescription()
            );
        }

        $researchSlug = $gameUnit->getResearchSlug();
        if ($researchSlug !== null && !$this->playerHasResearch($player, $researchSlug)) {
            throw new RuntimeException(
                "Cannot build {$gameUnit->getName()}: requires {$gameUnit->getResearchName()} research"
            );
        }
    }

    private function queueConstruction(WorldRegion $region, Player $player, GameUnit $gameUnit): void
    {
        $construction = Construction::create(
            $region,
            $player,
            $gameUnit->getGameUnitEnum(),
            1,
            $gameUnit->getTimestamp()
        );
        $this->constructionRepository->save($construction);

        $this->behaviorFactory->create($gameUnit)->onBuild($region, 1);
    }

    private function chargeResources(Player $player, GameUnit $gameUnit, int $multiplier): void
    {
        $this->deductResources(
            $player,
            $multiplier * $gameUnit->getCost()->getCash(),
            $multiplier * $gameUnit->getCost()->getWood(),
            $multiplier * $gameUnit->getCost()->getSteel()
        );
    }

    private function deductResources(Player $player, int $cash, int $wood, int $steel): void
    {
        $resources = $player->getResources();

        if ($cash > $resources->getCash()) {
            throw new RuntimeException("You don't have enough cash to do that.");
        }
        if ($wood > $resources->getWood()) {
            throw new RuntimeException("You don't have enough wood to do that.");
        }
        if ($steel > $resources->getSteel()) {
            throw new RuntimeException("You don't have enough steel to do that.");
        }

        $resources->setCash($resources->getCash() - $cash);
        $resources->setWood($resources->getWood() - $wood);
        $resources->setSteel($resources->getSteel() - $steel);

        $player->setResources($resources);
        $this->playerRepository->save($player);
    }

    private function getQueuedCount(WorldRegion $region, GameUnitEnum $gameUnit): int
    {
        $count = 0;
        foreach ($region->getConstructions() as $construction) {
            if ($construction->getGameUnit() === $gameUnit) {
                $count += $construction->getNumber();
            }
        }

        return $count;
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
}
