<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Federation
 */
class Federation
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
     * @var Player
     */
    private $founder;

    /**
     * @var string
     */
    private $leaderMessage;

    /**
     * @var int
     */
    private $cashbank = '0';

    /**
     * @var int
     */
    private $woodbank = '0';

    /**
     * @var int
     */
    private $steelbank = '0';

    /**
     * @var int
     */
    private $foodbank = '0';

    /**
     * @var int
     */
    private $regions;

    /**
     * @var int
     */
    private $networth;

    /**
     * @var World
     */
    private $world;

    /**
     * @var array
     */
    private $players = [];

    /**
     * @var array
     */
    private $federationApplications = [];

    /**
     * @var array
     */
    private $federationNews = [];

    /**
     * Federation constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->federationApplications = new ArrayCollection();
        $this->federationNews = new ArrayCollection();
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
     * @return Federation
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
     * Set founder
     *
     * @param Player $founder
     *
     * @return Federation
     */
    public function setFounder(Player $founder)
    {
        $this->founder = $founder;

        return $this;
    }

    /**
     * Get founder
     *
     * @return Player
     */
    public function getFounder()
    {
        return $this->founder;
    }

    /**
     * Set leaderMessage
     *
     * @param string $leaderMessage
     *
     * @return Federation
     */
    public function setLeaderMessage($leaderMessage)
    {
        $this->leaderMessage = $leaderMessage;

        return $this;
    }

    /**
     * Get leaderMessage
     *
     * @return string
     */
    public function getLeaderMessage()
    {
        return $this->leaderMessage;
    }

    /**
     * Set cashbank
     *
     * @param int $cashbank
     *
     * @return Federation
     */
    public function setCashbank($cashbank)
    {
        $this->cashbank = $cashbank;

        return $this;
    }

    /**
     * Get cashbank
     *
     * @return int
     */
    public function getCashbank()
    {
        return $this->cashbank;
    }

    /**
     * Set woodbank
     *
     * @param int $woodbank
     *
     * @return Federation
     */
    public function setWoodbank($woodbank)
    {
        $this->woodbank = $woodbank;

        return $this;
    }

    /**
     * Get woodbank
     *
     * @return int
     */
    public function getWoodbank()
    {
        return $this->woodbank;
    }

    /**
     * Set steelbank
     *
     * @param int $steelbank
     *
     * @return Federation
     */
    public function setSteelbank($steelbank)
    {
        $this->steelbank = $steelbank;

        return $this;
    }

    /**
     * Get steelbank
     *
     * @return int
     */
    public function getSteelbank()
    {
        return $this->steelbank;
    }

    /**
     * Set foodbank
     *
     * @param int $foodbank
     *
     * @return Federation
     */
    public function setFoodbank($foodbank)
    {
        $this->foodbank = $foodbank;

        return $this;
    }

    /**
     * Get foodbank
     *
     * @return int
     */
    public function getFoodbank()
    {
        return $this->foodbank;
    }

    /**
     * Set regions
     *
     * @param int $regions
     *
     * @return Federation
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
     * @return Federation
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

    /**
     * @return array
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param array $players
     */
    public function setPlayers($players)
    {
        $this->players = $players;
    }

    /**
     * @return array
     */
    public function getFederationNews()
    {
        return $this->federationNews;
    }

    /**
     * @param array $federationNews
     */
    public function setFederationNews($federationNews)
    {
        $this->federationNews = $federationNews;
    }
}
