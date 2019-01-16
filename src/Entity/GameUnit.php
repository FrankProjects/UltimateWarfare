<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Cost;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Income;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Upkeep;

/**
 * GameUnit
 */
class GameUnit
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $nameMulti;

    /**
     * @var string
     */
    private $rowName;

    /**
     * @var string
     */
    private $image;

    /**
     * @var int
     */
    private $networth;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $description;

    /**
     * @var GameUnitType
     */
    private $gameUnitType;

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

    /**
     * @var BattleStats
     */
    private $battleStats;

    /**
     * @var Cost
     */
    private $cost;

    /**
     * @var Income
     */
    private $income;

    /**
     * @var Upkeep
     */
    private $upkeep;

    /**
     * GameUnit constructor.
     */
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
     * Set nameMulti
     *
     * @param string $nameMulti
     */
    public function setNameMulti(string $nameMulti): void
    {
        $this->nameMulti = $nameMulti;
    }

    /**
     * Get nameMulti
     *
     * @return string
     */
    public function getNameMulti(): string
    {
        return $this->nameMulti;
    }

    /**
     * Set rowName
     *
     * @param string $rowName
     */
    public function setRowName(string $rowName): void
    {
        $this->rowName = $rowName;
    }

    /**
     * Get rowName
     *
     * @return string
     */
    public function getRowName(): string
    {
        return $this->rowName;
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
     * Set networth
     *
     * @param int $networth
     */
    public function setNetworth(int $networth): void
    {
        $this->networth = $networth;
    }

    /**
     * Get networth
     *
     * @return int
     */
    public function getNetworth(): int
    {
        return $this->networth;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Collection
     */
    public function getWorldRegionUnits(): Collection
    {
        return $this->worldRegionUnits;
    }

    /**
     * @param Collection $worldRegionUnits
     */
    public function setWorldRegionUnits(Collection $worldRegionUnits)
    {
        $this->worldRegionUnits = $worldRegionUnits;
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
    public function setConstructions(Collection $constructions)
    {
        $this->constructions = $constructions;
    }

    /**
     * @return GameUnitType
     */
    public function getGameUnitType(): GameUnitType
    {
        return $this->gameUnitType;
    }

    /**
     * @param GameUnitType $gameUnitType
     */
    public function setGameUnitType(GameUnitType $gameUnitType): void
    {
        $this->gameUnitType = $gameUnitType;
    }

    /**
     * @return BattleStats
     */
    public function getBattleStats(): BattleStats
    {
        return $this->battleStats;
    }

    /**
     * @param BattleStats $battleStats
     */
    public function setBattleStats(BattleStats $battleStats): void
    {
        $this->battleStats = $battleStats;
    }

    /**
     * @return Cost
     */
    public function getCost(): Cost
    {
        return $this->cost;
    }

    /**
     * @param Cost $cost
     */
    public function setCost(Cost $cost): void
    {
        $this->cost = $cost;
    }

    /**
     * @return Income
     */
    public function getIncome(): Income
    {
        return $this->income;
    }

    /**
     * @param Income $income
     */
    public function setIncome(Income $income): void
    {
        $this->income = $income;
    }

    /**
     * @return Upkeep
     */
    public function getUpkeep(): Upkeep
    {
        return $this->upkeep;
    }

    /**
     * @param Upkeep $upkeep
     */
    public function setUpkeep(Upkeep $upkeep): void
    {
        $this->upkeep = $upkeep;
    }
}
