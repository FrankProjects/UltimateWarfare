<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class WorldSector
{
    private ?int $id;
    private int $x;
    private int $y;
    private string $image;
    private World $world;
    private int $regionCount;

    /** @var Collection<WorldRegion> */
    private Collection $worldRegions;

    public function __construct()
    {
        $this->worldRegions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setX(int $x): void
    {
        $this->x = $x;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function setY(int $y): void
    {
        $this->y = $y;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    public function setWorldRegions(Collection $worldRegions): void
    {
        $this->worldRegions = $worldRegions;
    }

    public static function createForWorld(World $world, int $x, int $y): WorldSector
    {
        $worldSector = new WorldSector();
        $worldSector->setWorld($world);
        $worldSector->setX($x);
        $worldSector->setY($y);
        $worldSector->setImage('');

        return $worldSector;
    }

    public function setRegionCount(int $regionCount): void
    {
        $this->regionCount = $regionCount;
    }

    public function getRegionCount(): int
    {
        return $this->regionCount;
    }
}
