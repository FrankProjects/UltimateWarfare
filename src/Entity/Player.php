<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\Player\Income;
use FrankProjects\UltimateWarfare\Entity\Player\Notifications;
use FrankProjects\UltimateWarfare\Entity\Player\Resources;
use FrankProjects\UltimateWarfare\Entity\Player\Upkeep;

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
    const FEDERATION_HIERARCHY_RECRUIT = 1;

    /**
     * @var int
     */
    const FEDERATION_HIERARCHY_CAPTAIN = 3;

    /**
     * @var int
     */
    const FEDERATION_HIERARCHY_GENERAL = 10;

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
    private $networth = 0;

    /**
     * @var int
     */
    private $federationHierarchy = 0;

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
     * @var Income
     */
    private $income;

    /**
     * @var Notifications
     */
    private $notifications;

    /**
     * @var Resources
     */
    private $resources;

    /**
     * @var Upkeep
     */
    private $upkeep;

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
        $this->income = new Income();
        $this->notifications = new Notifications();
        $this->resources = new Resources();
        $this->upkeep = new Upkeep();
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
     * @return Collection|WorldRegion[]
     */
    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    /**
     * @return Collection|Fleet[]
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
     * @return Notifications
     */
    public function getNotifications(): Notifications
    {
        return $this->notifications;
    }

    /**
     * @param Notifications $notifications
     */
    public function setNotifications(Notifications $notifications): void
    {
        $this->notifications = $notifications;
    }

    /**
     * @return Income
     */
    public function getIncome(): Income
    {
        return $this->income;
    }

    /**
     * @param Income $income
     */
    public function setIncome(Income $income): void
    {
        $this->income = $income;
    }

    /**
     * @return Upkeep
     */
    public function getUpkeep(): Upkeep
    {
        return $this->upkeep;
    }

    /**
     * @param Upkeep $upkeep
     */
    public function setUpkeep(Upkeep $upkeep): void
    {
        $this->upkeep = $upkeep;
    }

    /**
     * @param User $user
     * @param string $name
     * @param World $world
     * @return Player
     */
    public static function create(User $user, string $name, World $world)
    {
        $resources = new Resources();
        $resources->setCash($world->getResources()->getCash());
        $resources->setWood($world->getResources()->getWood());
        $resources->setSteel($world->getResources()->getSteel());
        $resources->setFood($world->getResources()->getFood());

        $player = new Player();
        $player->setUser($user);
        $player->setName($name);
        $player->setWorld($world);
        $player->setTimestampJoined(time());
        $player->setTimestampUpdate(time());
        $player->setResources($resources);

        return $player;
    }

    /**
     * @return int
     */
    public function getRegionPrice(): int
    {
        return count($this->getWorldRegions()) * self::PRICE_PER_REGION;
    }

    /**
     * @return bool
     */
    public function canSurrender(): bool
    {
        if ($this->getTimestampJoined() + 3600 * 24 * 2 < time()) {
            return true;
        }

        return false;
    }
}
