<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;

class Fleet
{
    private int $id;
    private int $timestamp;
    private int $timestampArrive;
    private Player $player;
    private WorldRegion $worldRegion;
    private WorldRegion $targetWorldRegion;

    /** @var list<array{int, int}> Coordinates of the regions the fleet travels through, from source to target */
    private array $path = [];

    /** @var Collection<int, FleetUnit> */
    private Collection $fleetUnits;

    public function __construct()
    {
        $this->fleetUnits = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestampArrive(int $timestampArrive): void
    {
        $this->timestampArrive = $timestampArrive;
    }

    public function getTimestampArrive(): int
    {
        return $this->timestampArrive;
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

    public function getTargetWorldRegion(): WorldRegion
    {
        return $this->targetWorldRegion;
    }

    public function setTargetWorldRegion(WorldRegion $targetWorldRegion): void
    {
        $this->targetWorldRegion = $targetWorldRegion;
    }

    /**
     * @return list<array{int, int}>
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param list<array{int, int}> $path
     */
    public function setPath(array $path): void
    {
        $this->path = $path;
    }

    /**
     * @return Collection<int, FleetUnit>
     */
    public function getFleetUnits(): Collection
    {
        return $this->fleetUnits;
    }
}
