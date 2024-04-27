<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Research
{
    private int $id;
    private string $name;
    private string $image;
    private int $cost;
    private int $timestamp;
    private string $description;
    private bool $active = false;

    /** @var Collection<int, ResearchPlayer> */
    private Collection $researchPlayers;

    /** @var Collection<int, ResearchNeeds> */
    private Collection $researchNeeds;

    /** @var Collection<int, ResearchNeeds> */
    private Collection $requiredResearch;

    /** @var Collection<int, Operation> */
    private Collection $operations;

    public function __construct()
    {
        $this->researchPlayers = new ArrayCollection();
        $this->researchNeeds = new ArrayCollection();
        $this->requiredResearch = new ArrayCollection();
        $this->operations = new ArrayCollection();
    }

    public function setId(int $id): void
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

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return Collection<int, ResearchNeeds>
     */
    public function getResearchNeeds(): Collection
    {
        return $this->researchNeeds;
    }

    /**
     * @param Collection<int, ResearchNeeds> $researchNeeds
     */
    public function setResearchNeeds(Collection $researchNeeds): void
    {
        $this->researchNeeds = $researchNeeds;
    }

    /**
     * @return Collection<int, ResearchNeeds>
     */
    public function getRequiredResearch(): Collection
    {
        return $this->requiredResearch;
    }

    /**
     * @param Collection<int, ResearchNeeds> $requiredResearch
     */
    public function setRequiredResearch(Collection $requiredResearch): void
    {
        $this->requiredResearch = $requiredResearch;
    }

    /**
     * @return Collection<int, ResearchPlayer>
     */
    public function getResearchPlayers(): Collection
    {
        return $this->researchPlayers;
    }

    /**
     * @param Collection<int, ResearchPlayer> $researchPlayers
     */
    public function setResearchPlayers(Collection $researchPlayers): void
    {
        $this->researchPlayers = $researchPlayers;
    }

    /**
     * @return Collection<int, Operation>
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    /**
     * @param Collection<int, Operation> $operations
     */
    public function setOperations(Collection $operations): void
    {
        $this->operations = $operations;
    }
}
