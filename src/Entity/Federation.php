<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FrankProjects\UltimateWarfare\Entity\Federation\Resources;

class Federation
{
    private ?int $id;
    private string $name = '';
    private Player $founder;
    private string $leaderMessage = '';
    private int $regions = 0;
    private int $networth = 0;
    private World $world;

    /** @var Collection<int, Player> */
    private Collection $players;

    /** @var Collection<int, FederationApplication> */
    private Collection $federationApplications;

    /** @var Collection<int, FederationNews> */
    private Collection $federationNews;
    private Resources $resources;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->federationApplications = new ArrayCollection();
        $this->federationNews = new ArrayCollection();
        $this->resources = new Resources();
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

    public function setFounder(Player $founder): void
    {
        $this->founder = $founder;
    }

    public function getFounder(): Player
    {
        return $this->founder;
    }

    public function setLeaderMessage(string $leaderMessage): void
    {
        $this->leaderMessage = $leaderMessage;
    }

    public function getLeaderMessage(): string
    {
        return $this->leaderMessage;
    }

    public function setRegions(int $regions): void
    {
        $this->regions = $regions;
    }

    public function getRegions(): int
    {
        return $this->regions;
    }

    public function setNetworth(int $networth): void
    {
        $this->networth = $networth;
    }

    public function getNetworth(): int
    {
        return $this->networth;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    /**
     * @return Collection<int, FederationApplication>
     */
    public function getFederationApplications(): Collection
    {
        return $this->federationApplications;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @return Collection<int, FederationNews>
     */
    public function getFederationNews(): Collection
    {
        return $this->federationNews;
    }

    public function getResources(): Resources
    {
        return $this->resources;
    }

    public function setResources(Resources $resources): void
    {
        $this->resources = $resources;
    }

    public static function createForPlayer(Player $player, string $name): Federation
    {
        $federation = new Federation();
        $federation->setName($name);
        $federation->setFounder($player);
        $federation->setWorld($player->getWorld());
        $federation->setRegions(count($player->getWorldRegions()));
        $federation->setNetworth($player->getNetworth());

        return $federation;
    }
}
