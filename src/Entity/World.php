<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;
use FrankProjects\UltimateWarfare\Entity\World\Resources;
use RuntimeException;

class World
{
    private const STATUS_CREATED = 0;
    private const STATUS_RUNNING = 1;
    private const STATUS_FINISHED = 2;

    private ?int $id;
    private string $name = '';
    private string $image = '';
    private string $description = '';
    private int $status = 0;
    private bool $public = false;
    private int $maxPlayers = 0;
    private int $startTimestamp = 0;
    private int $endTimestamp = 0;
    private bool $market = true;
    private bool $federation = true;
    private int $federationLimit = 0;

    /**
     * @var Collection|WorldRegion[]
     */
    private $worldRegions = [];

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
    private Resources $resources;
    private MapConfiguration $mapConfiguration;

    public function __construct()
    {
        $this->worldRegions = new ArrayCollection();
        $this->worldSectors = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->marketItems = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->federations = new ArrayCollection();
        $this->resources = new Resources();
        $this->mapConfiguration = new MapConfiguration();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isValidStatus(int $status): bool
    {
        return in_array($status, self::getAllStatusOptions(), true);
    }

    public static function getAllStatusOptions(): array
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_RUNNING,
            self::STATUS_FINISHED
        ];
    }

    public function setStatus(int $status): void
    {
        if (!$this->isValidStatus($status)) {
            throw new RuntimeException("Invalid status {$status}");
        }

        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getPublic(): bool
    {
        return $this->public;
    }

    public function setMaxPlayers(int $maxPlayers): void
    {
        $this->maxPlayers = $maxPlayers;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function setStarttime(int $startTimestamp): void
    {
        $this->startTimestamp = $startTimestamp;
    }

    public function getStartTimestamp(): int
    {
        return $this->startTimestamp;
    }

    public function setEndTimestamp(int $endTimestamp): void
    {
        $this->endTimestamp = $endTimestamp;
    }

    public function getEndTimestamp(): int
    {
        return $this->endTimestamp;
    }

    public function setMarket(bool $market): void
    {
        $this->market = $market;
    }

    public function getMarket(): bool
    {
        return $this->market;
    }

    public function setFederation(bool $federation): void
    {
        $this->federation = $federation;
    }

    public function getFederation(): bool
    {
        return $this->federation;
    }

    public function getFederationLimit(): int
    {
        return $this->federationLimit;
    }

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
     * @return Collection|WorldSector[]
     */
    public function getWorldSectors(): Collection
    {
        return $this->worldSectors;
    }

    public function setWorldSectors(Collection $worldSectors): void
    {
        $this->worldSectors = $worldSectors;
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function setPlayers(Collection $players): void
    {
        $this->players = $players;
    }

    public function getMarketItems(): Collection
    {
        return $this->marketItems;
    }

    public function setMarketItems(Collection $marketItems): void
    {
        $this->marketItems = $marketItems;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }

    public function getFederations(): Collection
    {
        return $this->federations;
    }

    public function setFederations(Collection $federations): void
    {
        $this->federations = $federations;
    }

    public function getResources(): Resources
    {
        return $this->resources;
    }

    public function setResources(Resources $resources): void
    {
        $this->resources = $resources;
    }

    public function getMapConfiguration(): MapConfiguration
    {
        return $this->mapConfiguration;
    }

    public function setMapConfiguration(MapConfiguration $mapConfiguration): void
    {
        $this->mapConfiguration = $mapConfiguration;
    }

    public function isJoinableForUser(User $user): bool
    {
        if (count($this->getPlayers()) >= $this->getMaxPlayers()) {
            return false;
        }

        if (!in_array($this->getStatus(), [self::STATUS_CREATED, self::STATUS_RUNNING], true)) {
            return false;
        }

        foreach ($this->getPlayers() as $worldPlayer) {
            foreach ($user->getPlayers() as $player) {
                if ($player->getId() === $worldPlayer->getId()) {
                    return false;
                }
            }
        }

        if (count($this->getWorldSectors()) != 25) {
            return false;
        }

        if (count($this->getWorldRegions()) != 625) {
            return false;
        }

        return true;
    }
}
