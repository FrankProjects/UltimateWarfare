<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GameUnitType
{
    public const int GAME_UNIT_TYPE_BUILDINGS = 1;
    public const int GAME_UNIT_TYPE_DEFENCE_BUILDINGS = 2;
    public const int GAME_UNIT_TYPE_SPECIAL_BUILDINGS = 3;
    public const int GAME_UNIT_TYPE_UNITS = 4;
    public const int GAME_UNIT_TYPE_SPECIAL_UNITS = 5;

    private int $id;
    private string $name;
    private string $imageDir;

    /** @var Collection<int, GameUnit> */
    private Collection $gameUnits;

    public function __construct()
    {
        $this->gameUnits = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setImageDir(string $imageDir): void
    {
        $this->imageDir = $imageDir;
    }

    public function getImageDir(): string
    {
        return $this->imageDir;
    }

    /**
     * @return Collection<int, GameUnit>
     */
    public function getGameUnits(): Collection
    {
        return $this->gameUnits;
    }

    /**
     * @param Collection<int, GameUnit> $gameUnits
     */
    public function setGameUnits(Collection $gameUnits): void
    {
        $this->gameUnits = $gameUnits;
    }
}
