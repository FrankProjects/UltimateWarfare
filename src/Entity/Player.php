<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Player
 */
class Player
{
    /**
     * @var int
     */
    const PRICE_PER_REGION = 10000;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $timestampJoined;

    /**
     * @var int
     */
    private $timestampUpdate;

    /**
     * @var int
     */
    private $cash;

    /**
     * @var int
     */
    private $steel;

    /**
     * @var int
     */
    private $wood;

    /**
     * @var int
     */
    private $food;

    /**
     * @var int
     */
    private $regions = 0;

    /**
     * @var int
     */
    private $networth = 0;

    /**
     * @var bool
     */
    private $general = false;

    /**
     * @var bool
     */
    private $attacked = false;

    /**
     * @var bool
     */
    private $message = false;

    /**
     * @var bool
     */
    private $market = false;

    /**
     * @var bool
     */
    private $aid = false;

    /**
     * @var bool
     */
    private $news = false;

    /**
     * @var int
     */
    private $federationHierarchy = 0;

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
     * @var string
     */
    private $notepad = '';

    /**
     * @var User
     */
    private $user;

    /**
     * @var World
     */
    private $world;

    /**
     * @var Federation
     */
    private $federation;

    /**
     * @var Collection|Report[]
     */
    private $reports = [];

    /**
     * @var Collection|Construction[]
     */
    private $constructions = [];

    /**
     * @var Collection|WorldRegion[]
     */
    private $worldRegions = [];

    /**
     * @var Collection|Fleet[]
     */
    private $fleets = [];

    /**
     * @var Collection|MarketItem[]
     */
    private $marketItems = [];

    /**
     * @var Collection|Message[]
     */
    private $fromMessages = [];

    /**
     * @var Collection|Message[]
     */
    private $toMessages = [];

    /**
     * @var Collection|ResearchPlayer[]
     */
    private $playerResearch = [];

    /**
     * @var Collection|FederationApplication[]
     */
    private $federationApplications = [];

    /**
     * Player constructor.
     */
    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->constructions = new ArrayCollection();
        $this->worldRegions = new ArrayCollection();
        $this->fleets = new ArrayCollection();
        $this->marketItems = new ArrayCollection();
        $this->fromMessages = new ArrayCollection();
        $this->toMessages = new ArrayCollection();
        $this->playerResearch = new ArrayCollection();
        $this->federationApplications = new ArrayCollection();
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
    public function setName(string $name)
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
     * Set timestampJoined
     *
     * @param int $timestampJoined
     */
    public function setTimestampJoined(int $timestampJoined)
    {
        $this->timestampJoined = $timestampJoined;
    }

    /**
     * Get timestampJoined
     *
     * @return int
     */
    public function getTimestampJoined(): int
    {
        return $this->timestampJoined;
    }

    /**
     * Set timestampUpdate
     *
     * @param int $timestampUpdate
     */
    public function setTimestampUpdate(int $timestampUpdate)
    {
        $this->timestampUpdate = $timestampUpdate;
    }

    /**
     * Get timestampUpdate
     *
     * @return int
     */
    public function getTimestampUpdate(): int
    {
        return $this->timestampUpdate;
    }

    /**
     * Set cash
     *
     * @param int $cash
     */
    public function setCash(int $cash)
    {
        $this->cash = $cash;
    }

    /**
     * Get cash
     *
     * @return int
     */
    public function getCash(): int
    {
        return $this->cash;
    }

    /**
     * Set steel
     *
     * @param int $steel
     */
    public function setSteel(int $steel)
    {
        $this->steel = $steel;
    }

    /**
     * Get steel
     *
     * @return int
     */
    public function getSteel(): int
    {
        return $this->steel;
    }

    /**
     * Set wood
     *
     * @param int $wood
     */
    public function setWood(int $wood)
    {
        $this->wood = $wood;
    }

    /**
     * Get wood
     *
     * @return int
     */
    public function getWood(): int
    {
        return $this->wood;
    }

    /**
     * Set food
     *
     * @param int $food
     */
    public function setFood(int $food)
    {
        $this->food = $food;
    }

    /**
     * Get food
     *
     * @return int
     */
    public function getFood(): int
    {
        return $this->food;
    }

    /**
     * Set regions
     *
     * @param int $regions
     */
    public function setRegions(int $regions)
    {
        $this->regions = $regions;
    }

    /**
     * Get regions
     *
     * @return int
     */
    public function getRegions(): int
    {
        return $this->regions;
    }

    /**
     * Set networth
     *
     * @param int $networth
     */
    public function setNetworth(int $networth)
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
     * Set general
     *
     * @param bool $general
     */
    public function setGeneral(bool $general)
    {
        $this->general = $general;
    }

    /**
     * Get general
     *
     * @return bool
     */
    public function getGeneral(): bool
    {
        return $this->general;
    }

    /**
     * Set attacked
     *
     * @param bool $attacked
     */
    public function setAttacked(bool $attacked)
    {
        $this->attacked = $attacked;
    }

    /**
     * Get attacked
     *
     * @return bool
     */
    public function getAttacked(): bool
    {
        return $this->attacked;
    }

    /**
     * Set message
     *
     * @param bool $message
     */
    public function setMessage(bool $message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return bool
     */
    public function getMessage(): bool
    {
        return $this->message;
    }

    /**
     * Set market
     *
     * @param bool $market
     */
    public function setMarket(bool $market)
    {
        $this->market = $market;
    }

    /**
     * Get market
     *
     * @return bool
     */
    public function getMarket(): bool
    {
        return $this->market;
    }

    /**
     * Set aid
     *
     * @param bool $aid
     */
    public function setAid(bool $aid)
    {
        $this->aid = $aid;
    }

    /**
     * Get aid
     *
     * @return bool
     */
    public function getAid(): bool
    {
        return $this->aid;
    }

    /**
     * Set news
     *
     * @param bool $news
     */
    public function setNews(bool $news)
    {
        $this->news = $news;
    }

    /**
     * Get news
     *
     * @return bool
     */
    public function getNews(): bool
    {
        return $this->news;
    }

    /**
     * Set federationHierarchy
     *
     * @param int $federationHierarchy
     */
    public function setFederationHierarchy(int $federationHierarchy)
    {
        $this->federationHierarchy = $federationHierarchy;
    }

    /**
     * Get federationHierarchy
     *
     * @return int
     */
    public function getFederationHierarchy(): int
    {
        return $this->federationHierarchy;
    }

    /**
     * Set incomeCash
     *
     * @param int $incomeCash
     */
    public function setIncomeCash(int $incomeCash)
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
    public function setIncomeFood(int $incomeFood)
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
    public function setIncomeWood(int $incomeWood)
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
    public function setIncomeSteel(int $incomeSteel)
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
    public function setUpkeepCash(int $upkeepCash)
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
    public function setUpkeepFood(int $upkeepFood)
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
    public function setUpkeepWood(int $upkeepWood)
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
    public function setUpkeepSteel(int $upkeepSteel)
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
     * Set notepad
     *
     * @param string $notepad
     */
    public function setNotepad(string $notepad)
    {
        $this->notepad = $notepad;
    }

    /**
     * Get notepad
     *
     * @return string
     */
    public function getNotepad(): string
    {
        return $this->notepad;
    }

    /**
     * @return Collection
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * @return Collection
     */
    public function getConstructions(): Collection
    {
        return $this->constructions;
    }

    /**
     * @return Collection
     */
    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    /**
     * @return Collection
     */
    public function getFleets(): Collection
    {
        return $this->fleets;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
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
    public function setWorld(World $world)
    {
        $this->world = $world;
    }

    /**
     * @return Federation|null
     */
    public function getFederation(): ?Federation
    {
        return $this->federation;
    }

    /**
     * @param Federation|null $federation
     */
    public function setFederation(?Federation $federation)
    {
        $this->federation = $federation;
    }

    /**
     * @return Collection
     */
    public function getFromMessages(): Collection
    {
        return $this->fromMessages;
    }

    /**
     * @param Collection $fromMessages
     */
    public function setFromMessages(Collection $fromMessages)
    {
        $this->fromMessages = $fromMessages;
    }

    /**
     * @return Collection
     */
    public function getToMessages(): Collection
    {
        return $this->toMessages;
    }

    /**
     * @param Collection $toMessages
     */
    public function setToMessages(Collection $toMessages)
    {
        $this->toMessages = $toMessages;
    }

    /**
     * @return Collection
     */
    public function getPlayerResearch(): Collection
    {
        return $this->playerResearch;
    }

    /**
     * @param Collection $playerResearch
     */
    public function setPlayerResearches(Collection $playerResearch)
    {
        $this->playerResearch = $playerResearch;
    }

    /**
     * @return Collection
     */
    public function getFederationApplications(): Collection
    {
        return $this->federationApplications;
    }

    /**
     * @param Collection $federationApplications
     */
    public function setFederationApplications(Collection $federationApplications)
    {
        $this->federationApplications = $federationApplications;
    }

    /**
     * @return Collection
     */
    public function getMarketItems(): Collection
    {
        return $this->marketItems;
    }

    /**
     * @param Collection $marketItems
     */
    public function setMarketItems(Collection $marketItems): void
    {
        $this->marketItems = $marketItems;
    }

    /**
     * @param User $user
     * @param string $name
     * @param World $world
     * @return Player
     */
    public static function create(User $user, string $name, World $world)
    {
        $player = new Player();
        $player->setUser($user);
        $player->setName($name);
        $player->setWorld($world);
        $player->setTimestampJoined(time());
        $player->setTimestampUpdate(time());
        $player->setCash($world->getCash());
        $player->setWood($world->getWood());
        $player->setSteel($world->getSteel());
        $player->setFood($world->getFood());

        return $player;
    }

    /**
     * @return int
     */
    public function getRegionPrice(): int
    {
        return $this->getRegions() * self::PRICE_PER_REGION;
    }
}
