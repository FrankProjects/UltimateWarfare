<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * WorldRegion
 */
class WorldRegion
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $region;

    /**
     * @var int
     */
    private $x;

    /**
     * @var int
     */
    private $y;

    /**
     * @var int
     */
    private $rX;

    /**
     * @var int
     */
    private $rY;

    /**
     * @var string
     */
    private $image;

    /**
     * @var int
     */
    private $state = '0';

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $space = 1000;

    /**
     * @var int
     */
    private $population = 0;

    /**
     * @var WorldCountry
     */
    private $worldCountry;

    /**
     * @var WorldSector
     */
    private $worldSector;

    /**
     * @var Player|null
     */
    private $player;

    /**
     * @var Collection|WorldRegionUnit[]
     */
    private $worldRegionUnits = [];

    /**
     * @var Collection|Construction[]
     */
    private $constructions = [];

    /**
     * @var Collection|Fleet[]
     */
    private $fleets = [];

    /**
     * @var Collection|Fleet[]
     */
    private $targetFleets = [];

    /**
     * WorldRegion constructor.
     */
    public function __construct()
    {
        $this->worldRegionUnits = new ArrayCollection();
        $this->constructions = new ArrayCollection();
        $this->fleets = new ArrayCollection();
        $this->targetFleets = new ArrayCollection();
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
     * Set region
     *
     * @param int $region
     */
    public function setRegion(int $region): void
    {
        $this->region = $region;
    }

    /**
     * Get region
     *
     * @return int
     */
    public function getRegion(): int
    {
        return $this->region;
    }

    /**
     * Set x
     *
     * @param int $x
     */
    public function setX(int $x): void
    {
        $this->x = $x;
    }

    /**
     * Get x
     *
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param int $y
     */
    public function setY(int $y): void
    {
        $this->y = $y;
    }

    /**
     * Get y
     *
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * Set rX
     *
     * @param int $rX
     */
    public function setRX(int $rX): void
    {
        $this->rX = $rX;
    }

    /**
     * Get rX
     *
     * @return int
     */
    public function getRX(): int
    {
        return $this->rX;
    }

    /**
     * Set rY
     *
     * @param int $rY
     */
    public function setRY(int $rY): void
    {
        $this->rY = $rY;
    }

    /**
     * Get rY
     *
     * @return int
     */
    public function getRY(): int
    {
        return $this->rY;
    }

    /**
     * Set image
     *
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Set state
     *
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set space
     *
     * @param int $space
     */
    public function setSpace(int $space): void
    {
        $this->space = $space;
    }

    /**
     * Get space
     *
     * @return int
     */
    public function getSpace(): int
    {
        return $this->space;
    }

    /**
     * Set population
     *
     * @param int $population
     */
    public function setPopulation(int $population): void
    {
        $this->population = $population;
    }

    /**
     * Get population
     *
     * @return int
     */
    public function getPopulation(): int
    {
        return $this->population;
    }

    /**
     * @return Collection|WorldRegionUnit[]
     */
    public function getWorldRegionUnits(): Collection
    {
        return $this->worldRegionUnits;
    }

    /**
     * @param array $worldRegionUnits
     */
    public function setWorldRegionUnits(array $worldRegionUnits): void
    {
        $this->worldRegionUnits = $worldRegionUnits;
    }

    /**
     * @return WorldCountry
     */
    public function getWorldCountry(): WorldCountry
    {
        return $this->worldCountry;
    }

    /**
     * @param WorldCountry $worldCountry
     */
    public function setWorldCountry(WorldCountry $worldCountry): void
    {
        $this->worldCountry = $worldCountry;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param Player|null $player
     */
    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return Collection|Fleet[]
     */
    public function getFleets(): Collection
    {
        return $this->fleets;
    }

    /**
     * @param array $fleets
     */
    public function setFleets(array $fleets): void
    {
        $this->fleets = $fleets;
    }

    /**
     * @return WorldSector
     */
    public function getWorldSector(): WorldSector
    {
        return $this->worldSector;
    }

    /**
     * @param WorldSector $worldSector
     */
    public function setWorldSector(WorldSector $worldSector): void
    {
        $this->worldSector = $worldSector;
    }

    /**
     * @return Collection|Fleet[]
     */
    public function getTargetFleets(): Collection
    {
        return $this->targetFleets;
    }

    /**
     * @param array $targetFleets
     */
    public function setTargetFleets(array $targetFleets): void
    {
        $this->targetFleets = $targetFleets;
    }

    /**
     * @return Collection|Construction[]
     */
    public function getConstructions(): Collection
    {
        return $this->constructions;
    }

    /**
     * @param array $constructions
     */
    public function setConstructions(array $constructions): void
    {
        $this->constructions = $constructions;
    }
}
