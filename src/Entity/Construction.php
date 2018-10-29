<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Construction
 */
class Construction
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $number;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var WorldRegion
     */
    private $worldRegion;

    /**
     * @var GameUnit
     */
    private $gameUnit;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set number
     *
     * @param int $number
     */
    public function setNumber(int $number)
    {
        $this->number = $number;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player)
    {
        $this->player = $player;
    }

    /**
     * @return WorldRegion
     */
    public function getWorldRegion(): WorldRegion
    {
        return $this->worldRegion;
    }

    /**
     * @param WorldRegion $worldRegion
     */
    public function setWorldRegion(WorldRegion $worldRegion)
    {
        $this->worldRegion = $worldRegion;
    }

    /**
     * @return GameUnit
     */
    public function getGameUnit(): GameUnit
    {
        return $this->gameUnit;
    }

    /**
     * @param GameUnit $gameUnit
     */
    public function setGameUnit(GameUnit $gameUnit)
    {
        $this->gameUnit = $gameUnit;
    }

    /**
     * @param WorldRegion $worldRegion
     * @param Player $player
     * @param GameUnit $gameUnit
     * @param int $amount
     * @return Construction
     */
    public static function create(WorldRegion $worldRegion, Player $player, GameUnit $gameUnit, int $amount)
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
