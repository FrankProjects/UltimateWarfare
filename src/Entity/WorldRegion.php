<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * WorldRegion
 */
class WorldRegion
{
    const TYPE_WATER = 'water';
    const TYPE_BEACH = 'beach';
    const TYPE_FORREST = 'forrest';
    const TYPE_MOUNTAIN = 'mountain';

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
    private $z;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $state = 0;

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
     * @var World
     */
    private $world;

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
     * Set z
     *
     * @param int $z
     */
    public function setZ(int $z): void
    {
        $this->z = $z;
    }

    /**
     * Get z
     *
     * @return int
     */
    public function getZ(): int
    {
        return $this->z;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isValidType(string $type): bool
    {
        return in_array($type, self::getAllTypes());
    }

    /**
     * @return array
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

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        if (!$this->isValidType($type)) {
            throw new \RuntimeException("Invalid type {$type}");
        }

        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * @param Collection $worldRegionUnits
     */
    public function setWorldRegionUnits(Collection $worldRegionUnits): void
    {
        $this->worldRegionUnits = $worldRegionUnits;
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
     * @return Collection
     */
    public function getFleets(): Collection
    {
        return $this->fleets;
    }

    /**
     * @param Collection $fleets
     */
    public function setFleets(Collection $fleets): void
    {
        $this->fleets = $fleets;
    }

    /**
     * @return Collection
     */
    public function getTargetFleets(): Collection
    {
        return $this->targetFleets;
    }

    /**
     * @param Collection $targetFleets
     */
    public function setTargetFleets(Collection $targetFleets): void
    {
        $this->targetFleets = $targetFleets;
    }

    /**
     * @return Collection
     */
    public function getConstructions(): Collection
    {
        return $this->constructions;
    }

    /**
     * @param Collection $constructions
     */
    public function setConstructions(Collection $constructions): void
    {
        $this->constructions = $constructions;
    }

    /**
     * @return string
     */
    public function getRegionName(): string
    {
        return "{$this->getX()}, {$this->getY()}";
    }

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     * @param int $z
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     * @return WorldRegion
     */
    public static function createForWorld(World $world, int $x, int $y, int $z, WorldGeneratorConfiguration $worldGeneratorConfiguration): WorldRegion
    {
        $worldRegion = new WorldRegion();
        $worldRegion->setWorld($world);
        $worldRegion->setX($x);
        $worldRegion->setY($y);
        $worldRegion->setZ($z);
        $worldRegion->setType(self::getTypeFromConfiguration($worldGeneratorConfiguration, $z));
        $worldRegion->setSpace(self::getRandomSpaceFromType($worldRegion->getType()));
        $worldRegion->setPopulation($worldRegion->getSpace() * 10);

        return $worldRegion;
    }

    /**
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     * @param int $z
     * @return string
     */
    private static function getTypeFromConfiguration(WorldGeneratorConfiguration $worldGeneratorConfiguration, int $z): string
    {
        if ($z < $worldGeneratorConfiguration->getWaterLevel()) {
            return self::TYPE_WATER;
        } elseif ($z < $worldGeneratorConfiguration->getBeachLevel()) {
            return self::TYPE_BEACH;
        } elseif ($z < $worldGeneratorConfiguration->getForrestLevel()) {
            return self::TYPE_FORREST;
        }

        return self::TYPE_MOUNTAIN;
    }

    /**
     * @param string $type
     * @return int
     */
    private static function getRandomSpaceFromType(string $type): int
    {
        if ($type === self::TYPE_MOUNTAIN) {
            return rand(800, 1500);
        } elseif ($type === self::TYPE_FORREST) {
            return rand(1500, 2500);
        }

        return 0;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $playerName = '';
        $player = $this->getPlayer();
        if ($player !== null) {
            $playerName = $player->getName();
        }
        $array = [
            'id' => $this->getId(),
            'x' => $this->getX(),
            'y' => $this->getY(),
            'z' => $this->getZ(),
            'type' => $this->getType(),
            'owner' => $playerName,
            'units' => $this->getWorldRegionUnits()->toArray(),
            'structures' => []
        ];

        return $array;
    }
}
