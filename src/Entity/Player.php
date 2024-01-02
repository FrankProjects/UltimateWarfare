<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\Player\Income;
use FrankProjects\UltimateWarfare\Entity\Player\Notifications;
use FrankProjects\UltimateWarfare\Entity\Player\Resources;
use FrankProjects\UltimateWarfare\Entity\Player\Upkeep;

class Player
{
    private const int PRICE_PER_REGION = 10000;
    public const int FEDERATION_HIERARCHY_RECRUIT = 1;
    public const int FEDERATION_HIERARCHY_CAPTAIN = 3;
    public const int FEDERATION_HIERARCHY_GENERAL = 10;

    private ?int $id;
    private string $name;
    private int $timestampJoined;
    private int $timestampUpdate;
    private int $networth = 0;
    private int $federationHierarchy = 0;
    private string $notepad = '';
    private User $user;
    private World $world;
    private ?Federation $federation;

    /** @var Collection<int, Report> */
    private Collection $reports;

    /** @var Collection<int, Construction> */
    private Collection $constructions;

    /** @var Collection<int, WorldRegion> */
    private Collection $worldRegions;

    /** @var Collection<int, Fleet> */
    private Collection $fleets;

    /** @var Collection<int, MarketItem> */
    private Collection $marketItems;

    /** @var Collection<int, Message> */
    private Collection $fromMessages;

    /** @var Collection<int, Message> */
    private Collection $toMessages;

    /** @var Collection<int, ResearchPlayer> */
    private Collection $playerResearch;

    /** @var Collection<int, FederationApplication> */
    private Collection $federationApplications;

    private Income $income;
    private Notifications $notifications;
    private Resources $resources;
    private Upkeep $upkeep;

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

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function setTimestampJoined(int $timestampJoined): void
    {
        $this->timestampJoined = $timestampJoined;
    }

    public function getTimestampJoined(): int
    {
        return $this->timestampJoined;
    }

    public function setTimestampUpdate(int $timestampUpdate): void
    {
        $this->timestampUpdate = $timestampUpdate;
    }

    public function getTimestampUpdate(): int
    {
        return $this->timestampUpdate;
    }

    public function setNetworth(int $networth): void
    {
        $this->networth = $networth;
    }

    public function getNetworth(): int
    {
        return $this->networth;
    }

    public function setFederationHierarchy(int $federationHierarchy): void
    {
        $this->federationHierarchy = $federationHierarchy;
    }

    public function getFederationHierarchy(): int
    {
        return $this->federationHierarchy;
    }

    public function setNotepad(string $notepad): void
    {
        $this->notepad = $notepad;
    }

    public function getNotepad(): string
    {
        return $this->notepad;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * @return Collection<int, Construction>
     */
    public function getConstructions(): Collection
    {
        return $this->constructions;
    }

    /**
     * @return Collection<int, WorldRegion>
     */
    public function getWorldRegions(): Collection
    {
        return $this->worldRegions;
    }

    /**
     * @return Collection<int, Fleet>
     */
    public function getFleets(): Collection
    {
        return $this->fleets;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getFederation(): ?Federation
    {
        return $this->federation;
    }

    public function setFederation(?Federation $federation): void
    {
        $this->federation = $federation;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getFromMessages(): Collection
    {
        return $this->fromMessages;
    }

    /**
     * @param Collection<int, Message> $fromMessages
     */
    public function setFromMessages(Collection $fromMessages): void
    {
        $this->fromMessages = $fromMessages;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getToMessages(): Collection
    {
        return $this->toMessages;
    }

    /**
     * @param Collection<int, Message> $toMessages
     */
    public function setToMessages(Collection $toMessages): void
    {
        $this->toMessages = $toMessages;
    }

    /**
     * @return Collection<int, ResearchPlayer>
     */
    public function getPlayerResearch(): Collection
    {
        return $this->playerResearch;
    }

    /**
     * @param Collection<int, ResearchPlayer> $playerResearch
     */
    public function setPlayerResearches(Collection $playerResearch): void
    {
        $this->playerResearch = $playerResearch;
    }

    /**
     * @return Collection<int, FederationApplication>
     */
    public function getFederationApplications(): Collection
    {
        return $this->federationApplications;
    }

    /**
     * @param Collection<int, FederationApplication> $federationApplications
     */
    public function setFederationApplications(Collection $federationApplications): void
    {
        $this->federationApplications = $federationApplications;
    }

    /**
     * @return Collection<int, MarketItem>
     */
    public function getMarketItems(): Collection
    {
        return $this->marketItems;
    }

    /**
     * @param Collection<int, MarketItem> $marketItems
     */
    public function setMarketItems(Collection $marketItems): void
    {
        $this->marketItems = $marketItems;
    }

    public function getResources(): Resources
    {
        return $this->resources;
    }

    public function setResources(Resources $resources): void
    {
        $this->resources = $resources;
    }

    public function getNotifications(): Notifications
    {
        return $this->notifications;
    }

    public function setNotifications(Notifications $notifications): void
    {
        $this->notifications = $notifications;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function setIncome(Income $income): void
    {
        $this->income = $income;
    }

    public function getUpkeep(): Upkeep
    {
        return $this->upkeep;
    }

    public function setUpkeep(Upkeep $upkeep): void
    {
        $this->upkeep = $upkeep;
    }

    public static function create(User $user, string $name, World $world): Player
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

    public function getRegionPrice(): int
    {
        return count($this->getWorldRegions()) * self::PRICE_PER_REGION;
    }

    public function canSurrender(): bool
    {
        if ($this->getTimestampJoined() + 3600 * 24 * 2 < time()) {
            return true;
        }

        return false;
    }
}
