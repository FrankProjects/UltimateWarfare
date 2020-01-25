<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GameUnitType
{
    public const GAME_UNIT_TYPE_BUILDINGS = 1;
    public const GAME_UNIT_TYPE_DEFENCE_BUILDINGS = 2;
    public const GAME_UNIT_TYPE_SPECIAL_BUILDINGS = 3;
    public const GAME_UNIT_TYPE_UNITS = 4;
    public const GAME_UNIT_TYPE_SPECIAL_UNITS = 5;

    private ?int $id;
    private string $name;
    private string $imageDir;

    /**
     * @var Collection|GameUnit[]
     */
    private $gameUnits = [];

    public function __construct()
    {
        $this->gameUnits = new ArrayCollection();
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

    public function getGameUnits(): Collection
    {
        return $this->gameUnits;
    }

    public function setGameUnits(Collection $gameUnits): void
    {
        $this->gameUnits = $gameUnits;
    }
}
