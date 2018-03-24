<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * WorldCountry
 */
class WorldCountry
{
    /**
     * @var int
     */
    private $id;

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
    private $cX;

    /**
     * @var int
     */
    private $cY;

    /**
     * @var string
     */
    private $image;

    /**
     * @var WorldSector
     */
    private $worldSector;

    /**
     * @var Collection|WorldRegion[]
     */
    private $worldRegions = [];

    /**
     * WorldCountry constructor.
     */
    public function __construct()
    {
        $this->worldRegions = new ArrayCollection();
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
     * Set x
     *
     * @param int $x
     */
    public function setX(int $x)
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
    public function setY(int $y)
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
     * Set cX
     *
     * @param int $cX
     */
    public function setCX(int $cX)
    {
        $this->cX = $cX;
    }

    /**
     * Get cX
     *
     * @return int
     */
    public function getCX(): int
    {
        return $this->cX;
    }

    /**
     * Set cY
     *
     * @param int $cY
     */
    public function setCY(int $cY)
    {
        $this->cY = $cY;
    }

    /**
     * Get cY
     *
     * @return int
     */
    public function getCY(): int
    {
        return $this->cY;
    }

    /**
     * Set image
     *
     * @param string $image
     */
    public function setImage(string $image)
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
     * @return WorldSector
     */
    public function getWorldSector(): WorldSector
    {
        return $this->worldSector;
    }

    /**
     * @param WorldSector $worldSector
     */
    public function setWorldSector(WorldSector $worldSector)
    {
        $this->worldSector = $worldSector;
    }

    /**
     * @return Collection|WorldRegion[]
     */
    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    /**
     * @param array $worldRegions
     */
    public function setWorldRegions(array $worldRegions)
    {
        $this->worldRegions = $worldRegions;
    }
}
