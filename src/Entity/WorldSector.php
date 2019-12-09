<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * WorldSector
 */
class WorldSector
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
     * @var World
     */
    private $world;

    /**
     * @var Collection|WorldRegion[]
     */
    private $worldRegions = [];

    /**
     * WorldSector constructor.
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
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @param World $world
     */
    public function setWorld(World $world): void
    {
        $this->world = $world;
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
     * @param World $world
     * @param int $x
     * @param int $y
     * @return WorldSector
     */
    public static function createForWorld(World $world, int $x, int $y): WorldSector
    {
        $worldSector = new WorldSector();
        $worldSector->setWorld($world);
        $worldSector->setX($x);
        $worldSector->setY($y);
        $worldSector->setImage('');

        return $worldSector;
    }
}
