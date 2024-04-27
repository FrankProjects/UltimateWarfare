<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class WorldRegionUnit
{
    private int $id;
    private int $amount;
    private int $morale;
    private WorldRegion $worldRegion;
    private GameUnit $gameUnit;

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

    public function setMorale(int $morale): void
    {
        $this->morale = $morale;
    }

    public function getMorale(): int
    {
        return $this->morale;
    }

    public function getWorldRegion(): WorldRegion
    {
        return $this->worldRegion;
    }

    public function setWorldRegion(WorldRegion $worldRegion): void
    {
        $this->worldRegion = $worldRegion;
    }

    public function getGameUnit(): GameUnit
    {
        return $this->gameUnit;
    }

    public function setGameUnit(GameUnit $gameUnit): void
    {
        $this->gameUnit = $gameUnit;
    }

    public static function create(WorldRegion $worldRegion, GameUnit $gameUnit, int $amount): WorldRegionUnit
    {
        $worldRegionUnit = new WorldRegionUnit();
        $worldRegionUnit->setWorldRegion($worldRegion);
        $worldRegionUnit->setGameUnit($gameUnit);
        $worldRegionUnit->setAmount($amount);
        $worldRegionUnit->setMorale(100);

        return $worldRegionUnit;
    }
}
