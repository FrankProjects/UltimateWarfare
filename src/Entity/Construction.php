<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class Construction
{
    private ?int $id;
    private int $number;
    private int $timestamp;
    private Player $player;
    private WorldRegion $worldRegion;
    private GameUnit $gameUnit;

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

    public function getGameUnit(): GameUnit
    {
        return $this->gameUnit;
    }

    public function setGameUnit(GameUnit $gameUnit): void
    {
        $this->gameUnit = $gameUnit;
    }

    public static function create(WorldRegion $worldRegion, Player $player, GameUnit $gameUnit, int $amount): Construction
    {
        $construction = new Construction();
        $construction->setWorldRegion($worldRegion);
        $construction->setPlayer($player);
        $construction->setGameUnit($gameUnit);
        $construction->setNumber($amount);
        $construction->setTimestamp(time());

        return $construction;
    }
}
