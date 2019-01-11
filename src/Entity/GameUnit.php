<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats;

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
    private $priceCash;

    /**
     * @var int
     */
    private $priceWood;

    /**
     * @var int
     */
    private $priceSteel;

    /**
     * @var int
     */
    private $priceFood;

    /**
     * @var int
     */
    private $networth;

    /**
     * @var int
     */
    private $incomeCash = 0;

    /**
     * @var int
     */
    private $incomeFood = 0;

    /**
     * @var int
     */
    private $incomeWood = 0;

    /**
     * @var int
     */
    private $incomeSteel = 0;

    /**
     * @var int
     */
    private $upkeepCash = 0;

    /**
     * @var int
     */
    private $upkeepFood = 0;

    /**
     * @var int
     */
    private $upkeepWood = 0;

    /**
     * @var int
     */
    private $upkeepSteel = 0;

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
     * GameUnit constructor.
     */
    public function __construct()
    {
        $this->worldRegionUnits = new ArrayCollection();
        $this->constructions = new ArrayCollection();
        $this->fleetUnits = new ArrayCollection();
        $this->battleStats = new BattleStats();
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
     * Set incomeCash
     *
     * @param int $incomeCash
     */
    public function setIncomeCash(int $incomeCash): void
    {
        $this->incomeCash = $incomeCash;
    }

    /**
     * Get incomeCash
     *
     * @return int
     */
    public function getIncomeCash(): int
    {
        return $this->incomeCash;
    }

    /**
     * Set incomeFood
     *
     * @param int $incomeFood
     */
    public function setIncomeFood(int $incomeFood): void
    {
        $this->incomeFood = $incomeFood;
    }

    /**
     * Get incomeFood
     *
     * @return int
     */
    public function getIncomeFood(): int
    {
        return $this->incomeFood;
    }

    /**
     * Set incomeWood
     *
     * @param int $incomeWood
     */
    public function setIncomeWood(int $incomeWood): void
    {
        $this->incomeWood = $incomeWood;
    }

    /**
     * Get incomeWood
     *
     * @return int
     */
    public function getIncomeWood(): int
    {
        return $this->incomeWood;
    }

    /**
     * Set incomeSteel
     *
     * @param int $incomeSteel
     */
    public function setIncomeSteel(int $incomeSteel): void
    {
        $this->incomeSteel = $incomeSteel;
    }

    /**
     * Get incomeSteel
     *
     * @return int
     */
    public function getIncomeSteel(): int
    {
        return $this->incomeSteel;
    }

    /**
     * Set upkeepCash
     *
     * @param int $upkeepCash
     */
    public function setUpkeepCash(int $upkeepCash): void
    {
        $this->upkeepCash = $upkeepCash;
    }

    /**
     * Get upkeepCash
     *
     * @return int
     */
    public function getUpkeepCash(): int
    {
        return $this->upkeepCash;
    }

    /**
     * Set upkeepFood
     *
     * @param int $upkeepFood
     */
    public function setUpkeepFood(int $upkeepFood): void
    {
        $this->upkeepFood = $upkeepFood;
    }

    /**
     * Get upkeepFood
     *
     * @return int
     */
    public function getUpkeepFood(): int
    {
        return $this->upkeepFood;
    }

    /**
     * Set upkeepWood
     *
     * @param int $upkeepWood
     */
    public function setUpkeepWood(int $upkeepWood): void
    {
        $this->upkeepWood = $upkeepWood;
    }

    /**
     * Get upkeepWood
     *
     * @return int
     */
    public function getUpkeepWood(): int
    {
        return $this->upkeepWood;
    }

    /**
     * Set upkeepSteel
     *
     * @param int $upkeepSteel
     */
    public function setUpkeepSteel(int $upkeepSteel): void
    {
        $this->upkeepSteel = $upkeepSteel;
    }

    /**
     * Get upkeepSteel
     *
     * @return int
     */
    public function getUpkeepSteel(): int
    {
        return $this->upkeepSteel;
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
     * @return int
     */
    public function getPriceCash(): int
    {
        return $this->priceCash;
    }

    /**
     * @param int $priceCash
     */
    public function setPriceCash(int $priceCash): void
    {
        $this->priceCash = $priceCash;
    }

    /**
     * @return int
     */
    public function getPriceWood(): int
    {
        return $this->priceWood;
    }

    /**
     * @param int $priceWood
     */
    public function setPriceWood(int $priceWood): void
    {
        $this->priceWood = $priceWood;
    }

    /**
     * @return int
     */
    public function getPriceSteel(): int
    {
        return $this->priceSteel;
    }

    /**
     * @param int $priceSteel
     */
    public function setPriceSteel(int $priceSteel): void
    {
        $this->priceSteel = $priceSteel;
    }

    /**
     * @return int
     */
    public function getPriceFood(): int
    {
        return $this->priceFood;
    }

    /**
     * @param int $priceFood
     */
    public function setPriceFood(int $priceFood): void
    {
        $this->priceFood = $priceFood;
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
}
