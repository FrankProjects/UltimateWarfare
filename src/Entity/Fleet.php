<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Fleet
 */
class Fleet
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var int
     */
    private $timestampArrive;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var WorldRegion
     */
    private $worldRegion;

    /**
     * @var WorldRegion
     */
    private $targetWorldRegion;

    /**
     * @var Collection|FleetUnit[]
     */
    private $fleetUnits = [];

    /**
     * WorldRegion constructor.
     */
    public function __construct()
    {
        $this->fleetUnits = new ArrayCollection();
    }

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
     * Set timestampArrive
     *
     * @param int $timestampArrive
     */
    public function setTimestampArrive(int $timestampArrive)
    {
        $this->timestampArrive = $timestampArrive;
    }

    /**
     * Get timestampArrive
     *
     * @return int
     */
    public function getTimestampArrive(): int
    {
        return $this->timestampArrive;
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
     * @return WorldRegion
     */
    public function getTargetWorldRegion(): WorldRegion
    {
        return $this->targetWorldRegion;
    }

    /**
     * @param WorldRegion $targetWorldRegion
     */
    public function setTargetWorldRegion(WorldRegion $targetWorldRegion)
    {
        $this->targetWorldRegion = $targetWorldRegion;
    }

    /**
     * @return Collection
     */
    public function getFleetUnits(): Collection
    {
        return $this->fleetUnits;
    }

    /**
     * @param Collection $fleetUnits
     */
    public function setFleetUnits(Collection $fleetUnits)
    {
        $this->fleetUnits = $fleetUnits;
    }
}
