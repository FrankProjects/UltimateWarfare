<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;

class WorldRegionStackableUnit
{
    private int $id;
    private int $amount;
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

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
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
        int $amount
    ): WorldRegionStackableUnit {
        $worldRegionStackableUnit = new WorldRegionStackableUnit();
        $worldRegionStackableUnit->setWorldRegion($worldRegion);
        $worldRegionStackableUnit->setGameUnit($gameUnit);
        $worldRegionStackableUnit->setAmount($amount);

        // Keep the inverse side in sync, so already loaded collections include the new unit
        $worldRegion->addWorldRegionStackableUnit($worldRegionStackableUnit);

        return $worldRegionStackableUnit;
    }
}
