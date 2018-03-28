<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
    private $cashbank = 0;

    /**
     * @var int
     */
    private $woodbank = 0;

    /**
     * @var int
     */
    private $steelbank = 0;

    /**
     * @var int
     */
    private $foodbank = 0;

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
     * @var Collection|Player[]
     */
    private $players = [];

    /**
     * @var Collection|FederationApplication[]
     */
    private $federationApplications = [];

    /**
     * @var Collection|FederationNews[]
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
     * Set founder
     *
     * @param Player $founder
     */
    public function setFounder(Player $founder)
    {
        $this->founder = $founder;
    }

    /**
     * Get founder
     *
     * @return Player
     */
    public function getFounder(): Player
    {
        return $this->founder;
    }

    /**
     * Set leaderMessage
     *
     * @param string $leaderMessage
     */
    public function setLeaderMessage(string $leaderMessage)
    {
        $this->leaderMessage = $leaderMessage;
    }

    /**
     * Get leaderMessage
     *
     * @return string
     */
    public function getLeaderMessage(): string
    {
        return $this->leaderMessage;
    }

    /**
     * Set cashbank
     *
     * @param int $cashbank
     */
    public function setCashbank(int $cashbank)
    {
        $this->cashbank = $cashbank;
    }

    /**
     * Get cashbank
     *
     * @return int
     */
    public function getCashbank(): int
    {
        return $this->cashbank;
    }

    /**
     * Set woodbank
     *
     * @param int $woodbank
     */
    public function setWoodbank(int $woodbank)
    {
        $this->woodbank = $woodbank;
    }

    /**
     * Get woodbank
     *
     * @return int
     */
    public function getWoodbank(): int
    {
        return $this->woodbank;
    }

    /**
     * Set steelbank
     *
     * @param int $steelbank
     */
    public function setSteelbank(int $steelbank)
    {
        $this->steelbank = $steelbank;
    }

    /**
     * Get steelbank
     *
     * @return int
     */
    public function getSteelbank(): int
    {
        return $this->steelbank;
    }

    /**
     * Set foodbank
     *
     * @param int $foodbank
     */
    public function setFoodbank(int $foodbank)
    {
        $this->foodbank = $foodbank;
    }

    /**
     * Get foodbank
     *
     * @return int
     */
    public function getFoodbank(): int
    {
        return $this->foodbank;
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
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @param Collection $players
     */
    public function setPlayers(Collection $players)
    {
        $this->players = $players;
    }

    /**
     * @return Collection
     */
    public function getFederationNews(): Collection
    {
        return $this->federationNews;
    }

    /**
     * @param Collection $federationNews
     */
    public function setFederationNews(Collection $federationNews)
    {
        $this->federationNews = $federationNews;
    }
}
