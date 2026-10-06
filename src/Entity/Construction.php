<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;

class Construction
{
    private int $id;
    private int $number;
    private int $timestamp;
    private int $duration;
    private Player $player;
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

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * Effective build time in seconds, with any special-building build-speed bonus
     * already applied and locked in at the moment the construction was queued.
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
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
        Player $player,
        GameUnitEnum $gameUnit,
        int $amount,
        int $duration
    ): Construction {
        $construction = new Construction();
        $construction->setWorldRegion($worldRegion);
        $construction->setPlayer($player);
        $construction->setGameUnit($gameUnit);
        $construction->setNumber($amount);
        $construction->setTimestamp(time());
        $construction->setDuration($duration);

        // Keep the inverse side in sync, so an already loaded collection includes the new construction
        $worldRegion->addConstruction($construction);

        return $construction;
    }
}
