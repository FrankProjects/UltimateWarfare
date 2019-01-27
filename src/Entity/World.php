<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\World\Resources;

/**
 * Worlds
 */
class World
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $round;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $info;

    /**
     * @var string
     */
    private $status;

    /**
     * @var bool
     */
    private $public = false;

    /**
     * @var int
     */
    private $maxPlayers;

    /**
     * @var int
     */
    private $startTimestamp;

    /**
     * @var int
     */
    private $endTimestamp;

    /**
     * @var bool
     */
    private $market;

    /**
     * @var bool
     */
    private $federation;

    /**
     * @var int
     */
    private $fedLimit = 0;

    /**
     * @var Collection|WorldSector[]
     */
    private $worldSectors = [];

    /**
     * @var Collection|Player[]
     */
    private $players = [];

    /**
     * @var Collection|MarketItem[]
     */
    private $marketItems = [];

    /**
     * @var Collection|Message[]
     */
    private $messages = [];

    /**
     * @var Collection|Federation[]
     */
    private $federations = [];

    /**
     * @var Resources
     */
    private $resources;

    /**
     * World constructor.
     */
    public function __construct()
    {
        $this->worldSectors = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->marketItems = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->federations = new ArrayCollection();
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
     * Set round
     *
     * @param int $round
     */
    public function setRound(int $round): void
    {
        $this->round = $round;
    }

    /**
     * Get round
     *
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
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
     * Set info
     *
     * @param string $info
     */
    public function setInfo(string $info): void
    {
        $this->info = $info;
    }

    /**
     * Get info
     *
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set public
     *
     * @param bool $public
     */
    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    /**
     * Get public
     *
     * @return bool
     */
    public function getPublic(): bool
    {
        return $this->public;
    }

    /**
     * Set maxPlayers
     *
     * @param int $maxPlayers
     */
    public function setMaxPlayers(int $maxPlayers): void
    {
        $this->maxPlayers = $maxPlayers;
    }

    /**
     * Get maxPlayers
     *
     * @return int
     */
    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    /**
     * Set startTimestamp
     *
     * @param int $startTimestamp
     */
    public function setStarttime(int $startTimestamp): void
    {
        $this->startTimestamp = $startTimestamp;
    }

    /**
     * Get startTimestamp
     *
     * @return int
     */
    public function getStartTimestamp(): int
    {
        return $this->startTimestamp;
    }

    /**
     * Set endTimestamp
     *
     * @param int $endTimestamp
     */
    public function setEndTimestamp(int $endTimestamp): void
    {
        $this->endTimestamp = $endTimestamp;
    }

    /**
     * Get endTimestamp
     *
     * @return int
     */
    public function getEndTimestamp(): int
    {
        return $this->endTimestamp;
    }

    /**
     * Set market
     *
     * @param bool $market
     */
    public function setMarket(bool $market): void
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
     * Set federation
     *
     * @param bool $federation
     */
    public function setFederation(bool $federation): void
    {
        $this->federation = $federation;
    }

    /**
     * Get federation
     *
     * @return bool
     */
    public function getFederation(): bool
    {
        return $this->federation;
    }

    /**
     * Set fedLimit
     *
     * @param int $fedLimit
     */
    public function setFedLimit(int $fedLimit): void
    {
        $this->fedLimit = $fedLimit;
    }

    /**
     * Get fedLimit
     *
     * @return int
     */
    public function getFedLimit(): int
    {
        return $this->fedLimit;
    }

    /**
     * @return Collection
     */
    public function getWorldSectors(): Collection
    {
        return $this->worldSectors;
    }

    /**
     * @param Collection $worldSectors
     */
    public function setWorldSectors(Collection $worldSectors): void
    {
        $this->worldSectors = $worldSectors;
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
    public function setPlayers(Collection $players): void
    {
        $this->players = $players;
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
     * @return Collection
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param Collection $messages
     */
    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @return Collection
     */
    public function getFederations(): Collection
    {
        return $this->federations;
    }

    /**
     * @param Collection $federations
     */
    public function setFederations(Collection $federations): void
    {
        $this->federations = $federations;
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
}
