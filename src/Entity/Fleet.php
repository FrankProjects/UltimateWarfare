<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;

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
     * Fleet constructor.
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
     * @return Collection|FleetUnit[]
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

    /**
     * @param Player $player
     * @param WorldRegion $worldRegion
     * @param WorldRegion $targetWorldRegion
     * @return Fleet
     */
    public static function createForPlayer(Player $player, WorldRegion $worldRegion, WorldRegion $targetWorldRegion): Fleet
    {
        $distanceCalculator = new DistanceCalculator();
        $distance = $distanceCalculator->calculateDistance(
            $targetWorldRegion->getX(),
            $targetWorldRegion->getY(),
            $worldRegion->getX(),
            $worldRegion->getY()
        );

        $fleet = new Fleet();
        $fleet->setPlayer($player);
        $fleet->setWorldRegion($worldRegion);
        $fleet->setTargetWorldRegion($targetWorldRegion);
        $fleet->setTimestamp(time());
        $fleet->setTimestampArrive(time() + ($distance * 100));

        return $fleet;
    }
}
