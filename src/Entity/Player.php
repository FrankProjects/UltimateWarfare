<?php

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
     * @var GameAccount
     */
    private $gameAccount;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set timestampJoined
     *
     * @param int $timestampJoined
     */
    public function setTimestampJoined($timestampJoined)
    {
        $this->timestampJoined = $timestampJoined;
    }

    /**
     * Get timestampJoined
     *
     * @return int
     */
    public function getTimestampJoined()
    {
        return $this->timestampJoined;
    }

    /**
     * Set timestampUpdate
     *
     * @param int $timestampUpdate
     */
    public function setTimestampUpdate($timestampUpdate)
    {
        $this->timestampUpdate = $timestampUpdate;
    }

    /**
     * Get timestampUpdate
     *
     * @return int
     */
    public function getTimestampUpdate()
    {
        return $this->timestampUpdate;
    }

    /**
     * Set cash
     *
     * @param int $cash
     */
    public function setCash($cash)
    {
        $this->cash = $cash;
    }

    /**
     * Get cash
     *
     * @return int
     */
    public function getCash()
    {
        return $this->cash;
    }

    /**
     * Set steel
     *
     * @param int $steel
     */
    public function setSteel($steel)
    {
        $this->steel = $steel;
    }

    /**
     * Get steel
     *
     * @return int
     */
    public function getSteel()
    {
        return $this->steel;
    }

    /**
     * Set wood
     *
     * @param int $wood
     */
    public function setWood($wood)
    {
        $this->wood = $wood;
    }

    /**
     * Get wood
     *
     * @return int
     */
    public function getWood()
    {
        return $this->wood;
    }

    /**
     * Set food
     *
     * @param int $food
     */
    public function setFood($food)
    {
        $this->food = $food;
    }

    /**
     * Get food
     *
     * @return int
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set regions
     *
     * @param int $regions
     */
    public function setRegions($regions)
    {
        $this->regions = $regions;
    }

    /**
     * Get regions
     *
     * @return int
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * Set networth
     *
     * @param int $networth
     */
    public function setNetworth($networth)
    {
        $this->networth = $networth;
    }

    /**
     * Get networth
     *
     * @return int
     */
    public function getNetworth()
    {
        return $this->networth;
    }

    /**
     * Set general
     *
     * @param bool $general
     */
    public function setGeneral($general)
    {
        $this->general = $general;
    }

    /**
     * Get general
     *
     * @return bool
     */
    public function getGeneral()
    {
        return $this->general;
    }

    /**
     * Set attacked
     *
     * @param bool $attacked
     */
    public function setAttacked($attacked)
    {
        $this->attacked = $attacked;
    }

    /**
     * Get attacked
     *
     * @return bool
     */
    public function getAttacked()
    {
        return $this->attacked;
    }

    /**
     * Set message
     *
     * @param bool $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return bool
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set market
     *
     * @param bool $market
     */
    public function setMarket($market)
    {
        $this->market = $market;
    }

    /**
     * Get market
     *
     * @return bool
     */
    public function getMarket()
    {
        return $this->market;
    }

    /**
     * Set aid
     *
     * @param bool $aid
     */
    public function setAid($aid)
    {
        $this->aid = $aid;
    }

    /**
     * Get aid
     *
     * @return bool
     */
    public function getAid()
    {
        return $this->aid;
    }

    /**
     * Set news
     *
     * @param bool $news
     */
    public function setNews($news)
    {
        $this->news = $news;
    }

    /**
     * Get news
     *
     * @return bool
     */
    public function getNews()
    {
        return $this->news;
    }

    /**
     * Set federationHierarchy
     *
     * @param int $federationHierarchy
     */
    public function setFedlvl($federationHierarchy)
    {
        $this->federationHierarchy = $federationHierarchy;
    }

    /**
     * Get federationHierarchy
     *
     * @return int
     */
    public function getFederationHierarchy()
    {
        return $this->federationHierarchy;
    }

    /**
     * Set incomeCash
     *
     * @param int $incomeCash
     */
    public function setIncomeCash($incomeCash)
    {
        $this->incomeCash = $incomeCash;
    }

    /**
     * Get incomeCash
     *
     * @return int
     */
    public function getIncomeCash()
    {
        return $this->incomeCash;
    }

    /**
     * Set incomeFood
     *
     * @param int $incomeFood
     */
    public function setIncomeFood($incomeFood)
    {
        $this->incomeFood = $incomeFood;
    }

    /**
     * Get incomeFood
     *
     * @return int
     */
    public function getIncomeFood()
    {
        return $this->incomeFood;
    }

    /**
     * Set incomeWood
     *
     * @param int $incomeWood
     */
    public function setIncomeWood($incomeWood)
    {
        $this->incomeWood = $incomeWood;
    }

    /**
     * Get incomeWood
     *
     * @return int
     */
    public function getIncomeWood()
    {
        return $this->incomeWood;
    }

    /**
     * Set incomeSteel
     *
     * @param int $incomeSteel
     */
    public function setIncomeSteel($incomeSteel)
    {
        $this->incomeSteel = $incomeSteel;
    }

    /**
     * Get incomeSteel
     *
     * @return int
     */
    public function getIncomeSteel()
    {
        return $this->incomeSteel;
    }

    /**
     * Set upkeepCash
     *
     * @param int $upkeepCash
     */
    public function setUpkeepCash($upkeepCash)
    {
        $this->upkeepCash = $upkeepCash;
    }

    /**
     * Get upkeepCash
     *
     * @return int
     */
    public function getUpkeepCash()
    {
        return $this->upkeepCash;
    }

    /**
     * Set upkeepFood
     *
     * @param int $upkeepFood
     */
    public function setUpkeepFood($upkeepFood)
    {
        $this->upkeepFood = $upkeepFood;
    }

    /**
     * Get upkeepFood
     *
     * @return int
     */
    public function getUpkeepFood()
    {
        return $this->upkeepFood;
    }

    /**
     * Set upkeepWood
     *
     * @param int $upkeepWood
     */
    public function setUpkeepWood($upkeepWood)
    {
        $this->upkeepWood = $upkeepWood;
    }

    /**
     * Get upkeepWood
     *
     * @return int
     */
    public function getUpkeepWood()
    {
        return $this->upkeepWood;
    }

    /**
     * Set upkeepSteel
     *
     * @param int $upkeepSteel
     */
    public function setUpkeepSteel($upkeepSteel)
    {
        $this->upkeepSteel = $upkeepSteel;
    }

    /**
     * Get upkeepSteel
     *
     * @return int
     */
    public function getUpkeepSteel()
    {
        return $this->upkeepSteel;
    }

    /**
     * Set notepad
     *
     * @param string $notepad
     */
    public function setNotepad($notepad)
    {
        $this->notepad = $notepad;
    }

    /**
     * Get notepad
     *
     * @return string
     */
    public function getNotepad()
    {
        return $this->notepad;
    }

    /**
     * @return array
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @return array
     */
    public function getConstructions()
    {
        return $this->constructions;
    }

    /**
     * @return array
     */
    public function getWorldRegions()
    {
        return $this->worldRegions;
    }

    /**
     * @return array
     */
    public function getFleets()
    {
        return $this->fleets;
    }

    /**
     * @return GameAccount
     */
    public function getGameAccount()
    {
        return $this->gameAccount;
    }

    /**
     * @param GameAccount $gameAccount
     */
    public function setGameAccount($gameAccount)
    {
        $this->gameAccount = $gameAccount;
    }

    /**
     * @return World
     */
    public function getWorld()
    {
        return $this->world;
    }

    /**
     * @param World $world
     */
    public function setWorld($world)
    {
        $this->world = $world;
    }

    /**
     * @return Federation
     */
    public function getFederation()
    {
        return $this->federation;
    }

    /**
     * @param Federation $federation
     */
    public function setFederation($federation)
    {
        $this->federation = $federation;
    }

    /**
     * @param GameAccount $gameAccount
     * @param string $name
     * @param World $world
     * @return Player
     */
    public static function create(GameAccount $gameAccount, $name, World $world)
    {
        $player = new Player();
        $player->setGameAccount($gameAccount);
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
     * @return array
     */
    public function getFromMessages()
    {
        return $this->fromMessages;
    }

    /**
     * @param array $fromMessages
     */
    public function setFromMessages($fromMessages)
    {
        $this->fromMessages = $fromMessages;
    }

    /**
     * @return array
     */
    public function getToMessages()
    {
        return $this->toMessages;
    }

    /**
     * @param array $toMessages
     */
    public function setToMessages($toMessages)
    {
        $this->toMessages = $toMessages;
    }

    /**
     * @return array
     */
    public function getPlayerResearch()
    {
        return $this->playerResearch;
    }

    /**
     * @param array $playerResearch
     */
    public function setPlayerResearches($playerResearch)
    {
        $this->playerResearch = $playerResearch;
    }

    /**
     * @return array
     */
    public function getFederationApplications()
    {
        return $this->federationApplications;
    }

    /**
     * @param array $federationApplications
     */
    public function setFederationApplications($federationApplications)
    {
        $this->federationApplications = $federationApplications;
    }
}

