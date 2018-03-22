<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * WorldRegionUnit
 */
class WorldRegionUnit
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var int
     */
    private $morale;

    /**
     * @var WorldRegion
     */
    private $worldRegion;

    /**
     * @var GameUnit
     */
    private $gameUnit;

    /**
     * Set amount
     *
     * @param int $amount
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set morale
     *
     * @param int $morale
     */
    public function setMorale(int $morale)
    {
        $this->morale = $morale;
    }

    /**
     * Get morale
     *
     * @return int
     */
    public function getMorale(): int
    {
        return $this->morale;
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @param GameUnit $gameUnit
     * @param int $amount
     * @return WorldRegionUnit
     */
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
