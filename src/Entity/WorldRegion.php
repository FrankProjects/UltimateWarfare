<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use RuntimeException;

class WorldRegion
{
    public const string TYPE_DEEP_WATER = 'deep_water';
    public const string TYPE_WATER = 'water';
    public const string TYPE_SHALLOW_WATER = 'shallow_water';
    public const string TYPE_SAND = 'sand';
    public const string TYPE_GRASSLAND = 'grassland';
    public const string TYPE_FOREST = 'forest';
    public const string TYPE_HILLS = 'hills';
    public const string TYPE_MOUNTAIN = 'mountain';


    private int $id;
    private int $x;
    private int $y;
    private int $z;
    private string $type;
    private int $state = 0;
    private ?string $name;
    private int $space = 1000;
    private int $population = 0;
    private World $world;
    private ?Player $player;

    /**
     * @var Collection<int, WorldRegionStackableUnit>
     */
    private Collection $worldRegionStackableUnits;

    /**
     * @var Collection<int, WorldRegionLeveledUnit>
     */
    private Collection $worldRegionLeveledUnits;

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
        $this->worldRegionStackableUnits = new ArrayCollection();
        $this->worldRegionLeveledUnits = new ArrayCollection();
        $this->constructions = new ArrayCollection();
        $this->fleets = new ArrayCollection();
        $this->targetFleets = new ArrayCollection();
    }

    public function setId(int $id): void
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
            self::TYPE_DEEP_WATER,
            self::TYPE_WATER,
            self::TYPE_SHALLOW_WATER,
            self::TYPE_SAND,
            self::TYPE_GRASSLAND,
            self::TYPE_FOREST,
            self::TYPE_HILLS,
            self::TYPE_MOUNTAIN,
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

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
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
     * @return Collection<int, WorldRegionStackableUnit>
     */
    public function getWorldRegionStackableUnits(): Collection
    {
        return $this->worldRegionStackableUnits;
    }

    /**
     * @param Collection<int, WorldRegionStackableUnit> $worldRegionStackableUnits
     */
    public function setWorldRegionStackableUnits(Collection $worldRegionStackableUnits): void
    {
        $this->worldRegionStackableUnits = $worldRegionStackableUnits;
    }

    public function addWorldRegionStackableUnit(WorldRegionStackableUnit $worldRegionStackableUnit): void
    {
        $this->worldRegionStackableUnits->add($worldRegionStackableUnit);
    }

    public function removeWorldRegionStackableUnit(WorldRegionStackableUnit $worldRegionStackableUnit): void
    {
        $this->worldRegionStackableUnits->removeElement($worldRegionStackableUnit);
    }

    /**
     * @return Collection<int, WorldRegionLeveledUnit>
     */
    public function getWorldRegionLeveledUnits(): Collection
    {
        return $this->worldRegionLeveledUnits;
    }

    /**
     * @param Collection<int, WorldRegionLeveledUnit> $worldRegionLeveledUnits
     */
    public function setWorldRegionLeveledUnits(Collection $worldRegionLeveledUnits): void
    {
        $this->worldRegionLeveledUnits = $worldRegionLeveledUnits;
    }

    public function addWorldRegionLeveledUnit(WorldRegionLeveledUnit $worldRegionLeveledUnit): void
    {
        $this->worldRegionLeveledUnits->add($worldRegionLeveledUnit);
    }

    public function removeWorldRegionLeveledUnit(WorldRegionLeveledUnit $worldRegionLeveledUnit): void
    {
        $this->worldRegionLeveledUnits->removeElement($worldRegionLeveledUnit);
    }

    /**
     * Return the leveled building (Defense / Special) of the given type present in this
     * region, or null when the building is not present.
     */
    public function getLeveledUnit(GameUnitEnum $gameUnit): ?WorldRegionLeveledUnit
    {
        foreach ($this->worldRegionLeveledUnits as $worldRegionLeveledUnit) {
            if ($worldRegionLeveledUnit->getGameUnit() === $gameUnit) {
                return $worldRegionLeveledUnit;
            }
        }

        return null;
    }

    /**
     * Return the level of a leveled building (Defense / Special) present in this region,
     * or 0 when the building is not present.
     */
    public function getUnitLevel(GameUnitEnum $gameUnit): int
    {
        return $this->getLeveledUnit($gameUnit)?->getLevel() ?? 0;
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

    public function addConstruction(Construction $construction): void
    {
        $this->constructions->add($construction);
    }

    public function removeConstruction(Construction $construction): void
    {
        $this->constructions->removeElement($construction);
    }

    public function getRegionName(): string
    {
        return "{$this->getX()}, {$this->getY()}";
    }

    public static function createForWorld(
        World $world,
        int $x,
        int $y,
        int $z,
        string $type,
        int $space
    ): WorldRegion {
        $worldRegion = new WorldRegion();
        $worldRegion->setWorld($world);
        $worldRegion->setX($x);
        $worldRegion->setY($y);
        $worldRegion->setZ($z);
        $worldRegion->setType($type);
        $worldRegion->setSpace($space);
        $worldRegion->setPopulation($space * 10);

        return $worldRegion;
    }

    /**
     * @return array{
     *   id: int, x: int, y: int, z: int, type: string, owner: string,
     *   units: array<int, WorldRegionStackableUnit>, structures: array<int, WorldRegionLeveledUnit>
     * }
     */
    public function toArray(): array
    {
        $playerName = '';
        $player = $this->getPlayer();
        if ($player !== null) {
            $playerName = $player->getName();
        }
        return [
            'id' => $this->getId(),
            'x' => $this->getX(),
            'y' => $this->getY(),
            'z' => $this->getZ(),
            'type' => $this->getType(),
            'owner' => $playerName,
            'units' => $this->getWorldRegionStackableUnits()->toArray(),
            'structures' => $this->getWorldRegionLeveledUnits()->toArray()
        ];
    }
}
