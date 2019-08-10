<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\World\Resources;
use RuntimeException;

class World
{
    const STATUS_CREATED = 0;
    const STATUS_RUNNING = 1;
    const STATUS_FINISHED = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $round = 1;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var int
     */
    private $status = 0;

    /**
     * @var bool
     */
    private $public = false;

    /**
     * @var int
     */
    private $maxPlayers = 0;

    /**
     * @var int
     */
    private $startTimestamp = 0;

    /**
     * @var int
     */
    private $endTimestamp = 0;

    /**
     * @var bool
     */
    private $market = true;

    /**
     * @var bool
     */
    private $federation = true;

    /**
     * @var int
     */
    private $federationLimit = 0;

    /**
     * @var Collection|WorldRegion[]
     */
    private $worldRegions = [];

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
        $this->worldRegions = new ArrayCollection();
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param int $status
     * @return bool
     */
    public function isValidStatus(int $status): bool
    {
        return in_array($status, self::getAllStatusOptions());
    }
    /**
     * @return array
     */
    public static function getAllStatusOptions(): array
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_RUNNING,
            self::STATUS_FINISHED
        ];
    }

    /**
     * Set status
     *
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        if (!$this->isValidStatus($status)) {
            throw new RuntimeException("Invalid status {$status}");
        }

        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
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
     * @return int
     */
    public function getFederationLimit(): int
    {
        return $this->federationLimit;
    }

    /**
     * @param int $federationLimit
     */
    public function setFederationLimit(int $federationLimit): void
    {
        $this->federationLimit = $federationLimit;
    }

    /**
     * @return Collection|WorldRegion[]
     */
    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    /**
     * @param Collection|WorldRegion[] $worldRegions
     */
    public function setWorldRegions($worldRegions): void
    {
        $this->worldRegions = $worldRegions;
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
