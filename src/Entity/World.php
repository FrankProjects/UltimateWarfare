<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
     * @var int
     */
    private $cash;

    /**
     * @var int
     */
    private $wood;

    /**
     * @var int
     */
    private $steel;

    /**
     * @var int
     */
    private $food;

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
     * World constructor.
     */
    public function __construct()
    {
        $this->worldSectors = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->marketItems = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->federations = new ArrayCollection();
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
    public function setRound(int $round)
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
     * Set image
     *
     * @param string $image
     */
    public function setImage(string $image)
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
    public function setInfo(string $info)
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
    public function setStatus(string $status)
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
    public function setPublic(bool $public)
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
    public function setMaxPlayers(int $maxPlayers)
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
    public function setStarttime(int $startTimestamp)
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
    public function setEndTimestamp(int $endTimestamp)
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
     * Set federation
     *
     * @param bool $federation
     */
    public function setFederation(bool $federation)
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
    public function setFedLimit(int $fedLimit)
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
    public function setWorldSectors(Collection $worldSectors)
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
    public function setPlayers(Collection $players)
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
    public function setMarketItems(Collection $marketItems)
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
    public function setMessages(Collection $messages)
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
    public function setFederations(Collection $federations)
    {
        $this->federations = $federations;
    }
}
