<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Cost;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Income;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Upkeep;

class GameUnit
{
    private ?int $id;
    private string $name;
    private string $nameMulti;
    private string $rowName;
    private string $image;
    private int $networth;
    private int $timestamp;
    private string $description;
    private GameUnitType $gameUnitType;

    /**
     * @var Collection|WorldRegionUnit[]
     */
    private $worldRegionUnits = [];

    /**
     * @var Collection|Construction[]
     */
    private $constructions = [];

    /**
     * @var Collection|FleetUnit[]
     */
    private $fleetUnits = [];
    private BattleStats $battleStats;
    private Cost $cost;
    private Income $income;
    private Upkeep $upkeep;

    public function __construct()
    {
        $this->worldRegionUnits = new ArrayCollection();
        $this->constructions = new ArrayCollection();
        $this->fleetUnits = new ArrayCollection();
        $this->battleStats = new BattleStats();
        $this->cost = new Cost();
        $this->income = new Income();
        $this->upkeep = new Upkeep();
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

    public function setNameMulti(string $nameMulti): void
    {
        $this->nameMulti = $nameMulti;
    }

    public function getNameMulti(): string
    {
        return $this->nameMulti;
    }

    public function setRowName(string $rowName): void
    {
        $this->rowName = $rowName;
    }

    public function getRowName(): string
    {
        return $this->rowName;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setNetworth(int $networth): void
    {
        $this->networth = $networth;
    }

    public function getNetworth(): int
    {
        return $this->networth;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGameUnitType(): GameUnitType
    {
        return $this->gameUnitType;
    }

    public function getBattleStats(): BattleStats
    {
        return $this->battleStats;
    }

    public function getCost(): Cost
    {
        return $this->cost;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getUpkeep(): Upkeep
    {
        return $this->upkeep;
    }
}
