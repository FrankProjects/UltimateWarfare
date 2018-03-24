<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @var bool
     */
    private $fedlvl = '0';

    /**
     * @var int
     */
    private $incomeCash = '0';

    /**
     * @var int
     */
    private $incomeFood = '0';

    /**
     * @var int
     */
    private $incomeWood = '0';

    /**
     * @var int
     */
    private $incomeSteel = '0';

    /**
     * @var int
     */
    private $upkeepCash = '0';

    /**
     * @var int
     */
    private $upkeepFood = '0';

    /**
     * @var int
     */
    private $upkeepWood = '0';

    /**
     * @var int
     */
    private $upkeepSteel = '0';

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
     * @var array
     */
    private $reports = [];

    /**
     * @var array
     */
    private $constructions = [];

    /**
     * @var array
     */
    private $worldRegions = [];

    /**
     * @var array
     */
    private $fleets = [];

    /**
     * @var array
     */
    private $marketItems = [];

    /**
     * @var array
     */
    private $fromMessages = [];

    /**
     * @var array
     */
    private $toMessages = [];

    /**
     * @var array
     */
    private $playerResearch = [];

    /**
     * @var array
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
     *
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     *
     * @return Player
     */
    public function setTimestampJoined($timestampJoined)
    {
        $this->timestampJoined = $timestampJoined;

        return $this;
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
     *
     * @return Player
     */
    public function setTimestampUpdate($timestampUpdate)
    {
        $this->timestampUpdate = $timestampUpdate;

        return $this;
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
     *
     * @return Player
     */
    public function setCash($cash)
    {
        $this->cash = $cash;

        return $this;
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
     *
     * @return Player
     */
    public function setSteel($steel)
    {
        $this->steel = $steel;

        return $this;
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
     *
     * @return Player
     */
    public function setWood($wood)
    {
        $this->wood = $wood;

        return $this;
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
     *
     * @return Player
     */
    public function setFood($food)
    {
        $this->food = $food;

        return $this;
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
     *
     * @return Player
     */
    public function setRegions($regions)
    {
        $this->regions = $regions;

        return $this;
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
     *
     * @return Player
     */
    public function setNetworth($networth)
    {
        $this->networth = $networth;

        return $this;
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
     *
     * @return Player
     */
    public function setGeneral($general)
    {
        $this->general = $general;

        return $this;
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
     *
     * @return Player
     */
    public function setAttacked($attacked)
    {
        $this->attacked = $attacked;

        return $this;
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
     *
     * @return Player
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
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
     *
     * @return Player
     */
    public function setMarket($market)
    {
        $this->market = $market;

        return $this;
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
     *
     * @return Player
     */
    public function setAid($aid)
    {
        $this->aid = $aid;

        return $this;
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
     *
     * @return Player
     */
    public function setNews($news)
    {
        $this->news = $news;

        return $this;
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
     * Set fedlvl
     *
     * @param bool $fedlvl
     *
     * @return Player
     */
    public function setFedlvl($fedlvl)
    {
        $this->fedlvl = $fedlvl;

        return $this;
    }

    /**
     * Get fedlvl
     *
     * @return bool
     */
    public function getFedlvl()
    {
        return $this->fedlvl;
    }

    /**
     * Set incomeCash
     *
     * @param int $incomeCash
     *
     * @return Player
     */
    public function setIncomeCash($incomeCash)
    {
        $this->incomeCash = $incomeCash;

        return $this;
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
     *
     * @return Player
     */
    public function setIncomeFood($incomeFood)
    {
        $this->incomeFood = $incomeFood;

        return $this;
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
     *
     * @return Player
     */
    public function setIncomeWood($incomeWood)
    {
        $this->incomeWood = $incomeWood;

        return $this;
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
     *
     * @return Player
     */
    public function setIncomeSteel($incomeSteel)
    {
        $this->incomeSteel = $incomeSteel;

        return $this;
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
     *
     * @return Player
     */
    public function setUpkeepCash($upkeepCash)
    {
        $this->upkeepCash = $upkeepCash;

        return $this;
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
     *
     * @return Player
     */
    public function setUpkeepFood($upkeepFood)
    {
        $this->upkeepFood = $upkeepFood;

        return $this;
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
     *
     * @return Player
     */
    public function setUpkeepWood($upkeepWood)
    {
        $this->upkeepWood = $upkeepWood;

        return $this;
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
     *
     * @return Player
     */
    public function setUpkeepSteel($upkeepSteel)
    {
        $this->upkeepSteel = $upkeepSteel;

        return $this;
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
     *
     * @return Player
     */
    public function setNotepad($notepad)
    {
        $this->notepad = $notepad;

        return $this;
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
     * Set mission
     *
     * @param bool $mission
     *
     * @return Player
     */
    public function setMission($mission)
    {
        $this->mission = $mission;

        return $this;
    }

    /**
     * Get mission
     *
     * @return bool
     */
    public function getMission()
    {
        return $this->mission;
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

