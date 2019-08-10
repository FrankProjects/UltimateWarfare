<?php

declare(strict_types=1);

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
     * @return Collection
     */
    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    /**
     * @param Collection $worldRegions
     */
    public function setWorldRegions(Collection $worldRegions): void
    {
        $this->worldRegions = $worldRegions;
    }

    /**
     * @param WorldSector $worldSector
     * @param int $x
     * @param int $y
     * @return WorldCountry
     */
    public static function createForWorldSector(WorldSector $worldSector, int $x, int $y): WorldCountry
    {
        $worldCountry = new WorldCountry();
        $worldCountry->setWorldSector($worldSector);
        $worldCountry->setX($x);
        $worldCountry->setY($y);
        $worldCountry->setImage('');

        return $worldCountry;
    }
}
