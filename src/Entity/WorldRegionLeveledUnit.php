<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;

/**
 * A leveled building (Defense / Special) present in a region. Unlike a stackable
 * WorldRegionStackableUnit it is always a single instance; its strength and durability are
 * driven by level (1-10) and health instead of an amount.
 */
class WorldRegionLeveledUnit
{
    private int $id;
    private int $level;
    private int $health;
    private WorldRegion $worldRegion;
    private GameUnitEnum $gameUnit;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setHealth(int $health): void
    {
        $this->health = $health;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function getWorldRegion(): WorldRegion
    {
        return $this->worldRegion;
    }

    public function setWorldRegion(WorldRegion $worldRegion): void
    {
        $this->worldRegion = $worldRegion;
    }

    public function getGameUnit(): GameUnitEnum
    {
        return $this->gameUnit;
    }

    public function setGameUnit(GameUnitEnum $gameUnit): void
    {
        $this->gameUnit = $gameUnit;
    }

    public static function create(
        WorldRegion $worldRegion,
        GameUnitEnum $gameUnit,
        int $level,
        int $health
    ): WorldRegionLeveledUnit {
        $worldRegionLeveledUnit = new WorldRegionLeveledUnit();
        $worldRegionLeveledUnit->setWorldRegion($worldRegion);
        $worldRegionLeveledUnit->setGameUnit($gameUnit);
        $worldRegionLeveledUnit->setLevel($level);
        $worldRegionLeveledUnit->setHealth($health);

        // Keep the inverse side in sync, so already loaded collections include the new unit
        $worldRegion->addWorldRegionLeveledUnit($worldRegionLeveledUnit);

        return $worldRegionLeveledUnit;
    }
}
