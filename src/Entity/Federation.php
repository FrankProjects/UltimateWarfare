<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\Federation\Resources;

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
    private $name = '';

    /**
     * @var Player
     */
    private $founder;

    /**
     * @var string
     */
    private $leaderMessage = '';

    /**
     * @var int
     */
    private $regions = 0;

    /**
     * @var int
     */
    private $networth = 0;

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
     * @var Resources
     */
    private $resources;

    /**
     * Federation constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->federationApplications = new ArrayCollection();
        $this->federationNews = new ArrayCollection();
        $this->resources = new Resources();
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
     * Set founder
     *
     * @param Player $founder
     */
    public function setFounder(Player $founder): void
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
    public function setLeaderMessage(string $leaderMessage): void
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
     * Set regions
     *
     * @param int $regions
     */
    public function setRegions(int $regions): void
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
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @param World $world
     */
    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    /**
     * @return Collection|FederationApplication[]
     */
    public function getFederationApplications(): Collection
    {
        return $this->federationApplications;
    }

    /**
     * @param Collection $federationApplications
     */
    public function setFederationApplications(Collection $federationApplications): void
    {
        $this->federationApplications = $federationApplications;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @param Collection $players
     */
    public function setPlayers(Collection $players): void
    {
        $this->players = $players;
    }

    /**
     * @return Collection|FederationNews[]
     */
    public function getFederationNews(): Collection
    {
        return $this->federationNews;
    }

    /**
     * @param Collection $federationNews
     */
    public function setFederationNews(Collection $federationNews): void
    {
        $this->federationNews = $federationNews;
    }

    /**
     * @return Resources
     */
    public function getResources(): Resources
    {
        return $this->resources;
    }

    /**
     * @param Resources $resources
     */
    public function setResources(Resources $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * @param Player $player
     * @param string $name
     * @return Federation
     */
    public static function createForPlayer(Player $player, string $name): Federation
    {
        $federation = new Federation();
        $federation->setName($name);
        $federation->setFounder($player);
        $federation->setWorld($player->getWorld());
        $federation->setRegions($player->getRegions());
        $federation->setNetworth($player->getNetworth());

        return $federation;
    }
}
