<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;

class WorldRegion
{
    public const TYPE_WATER = 'water';
    public const TYPE_BEACH = 'beach';
    public const TYPE_FORREST = 'forrest';
    public const TYPE_MOUNTAIN = 'mountain';

    private ?int $id;
    private int $x;
    private int $y;
    private int $z;
    private string $type;
    private int $state = 0;
    private ?string $name;
    private int $space = 1000;
    private int $population = 0;
    private World $world;
    private WorldSector $worldSector;
    private ?Player $player;

    /**
     * @var Collection<int, WorldRegionUnit>
     */
    private Collection $worldRegionUnits;

    /**
     * @var Collection<int, Construction>
     */
    private Collection $constructions;

    /**
     * @var Collection<int, Fleet>
     */
    private Collection $fleets;

    /**
     * @var Collection<int, Fleet>
     */
    private Collection $targetFleets;

    public function __construct()
    {
        $this->worldRegionUnits = new ArrayCollection();
        $this->constructions = new ArrayCollection();
        $this->fleets = new ArrayCollection();
        $this->targetFleets = new ArrayCollection();
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function getZ(): int
    {
        return $this->z;
    }

    public function setZ(int $z): void
    {
        $this->z = $z;
    }

    public function isValidType(string $type): bool
    {
        return in_array($type, self::getAllTypes(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_WATER,
            self::TYPE_BEACH,
            self::TYPE_FORREST,
            self::TYPE_MOUNTAIN
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!$this->isValidType($type)) {
            throw new RuntimeException("Invalid type {$type}");
        }

        $this->type = $type;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSpace(int $space): void
    {
        $this->space = $space;
    }

    public function getSpace(): int
    {
        return $this->space;
    }

    public function setPopulation(int $population): void
    {
        $this->population = $population;
    }

    public function getPopulation(): int
    {
        return $this->population;
    }

    /**
     * @return Collection<int, WorldRegionUnit>
     */
    public function getWorldRegionUnits(): Collection
    {
        return $this->worldRegionUnits;
    }

    /**
     * @param Collection<int, WorldRegionUnit> $worldRegionUnits
     */
    public function setWorldRegionUnits(Collection $worldRegionUnits): void
    {
        $this->worldRegionUnits = $worldRegionUnits;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return Collection<int, Fleet>
     */
    public function getFleets(): Collection
    {
        return $this->fleets;
    }

    /**
     * @param Collection<int, Fleet> $fleets
     */
    public function setFleets(Collection $fleets): void
    {
        $this->fleets = $fleets;
    }

    public function getWorldSector(): WorldSector
    {
        return $this->worldSector;
    }

    public function setWorldSector(WorldSector $worldSector): void
    {
        $this->worldSector = $worldSector;
    }

    /**
     * @return Collection<int, Fleet>
     */
    public function getTargetFleets(): Collection
    {
        return $this->targetFleets;
    }

    /**
     * @return Collection<int, Construction>
     */
    public function getConstructions(): Collection
    {
        return $this->constructions;
    }

    public function getRegionName(): string
    {
        return "{$this->getX()}, {$this->getY()}";
    }

    public static function createForWorldSector(
        WorldSector $worldSector,
        int $x,
        int $y,
        int $z,
        string $type,
        int $space
    ): WorldRegion {
        $worldRegion = new WorldRegion();
        $worldRegion->setWorld($worldSector->getWorld());
        $worldRegion->setWorldSector($worldSector);
        $worldRegion->setX($x);
        $worldRegion->setY($y);
        $worldRegion->setZ($z);
        $worldRegion->setType($type);
        $worldRegion->setSpace($space);
        $worldRegion->setPopulation($space * 10);

        return $worldRegion;
    }
}
