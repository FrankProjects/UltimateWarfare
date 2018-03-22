<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Research
 */
class Research
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
     * @var string
     */
    private $pic;

    /**
     * @var int
     */
    private $cost;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var Collection|ResearchPlayer[]
     */
    private $researchPlayers = [];

    /**
     * @var Collection|ResearchNeeds[]
     */
    private $researchNeeds = [];

    /**
     * @var Collection|ResearchNeeds[]
     */
    private $requiredResearch = [];

    /**
     * Research constructor.
     */
    public function __construct()
    {
        $this->researchPlayers = new ArrayCollection();
        $this->researchNeeds = new ArrayCollection();
        $this->requiredResearch = new ArrayCollection();
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
     * Set pic
     *
     * @param string $pic
     */
    public function setPic(string $pic)
    {
        $this->pic = $pic;
    }

    /**
     * Get pic
     *
     * @return string
     */
    public function getPic(): string
    {
        return $this->pic;
    }

    /**
     * Set cost
     *
     * @param int $cost
     */
    public function setCost(int $cost)
    {
        $this->cost = $cost;
    }

    /**
     * Get cost
     *
     * @return int
     */
    public function getCost(): int
    {
        return $this->cost;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set active
     *
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return Collection|ResearchNeeds[]
     */
    public function getResearchNeeds()
    {
        return $this->researchNeeds;
    }

    /**
     * @param array $researchNeeds
     */
    public function setResearchNeeds(array $researchNeeds)
    {
        $this->researchNeeds = $researchNeeds;
    }

    /**
     * @return Collection|ResearchNeeds[]
     */
    public function getRequiredResearch(): Collection
    {
        return $this->requiredResearch;
    }

    /**
     * @param array $requiredResearch
     */
    public function setRequiredResearch(array $requiredResearch)
    {
        $this->requiredResearch = $requiredResearch;
    }

    /**
     * @return Collection|ResearchPlayer[]
     */
    public function getResearchPlayers(): Collection
    {
        return $this->researchPlayers;
    }

    /**
     * @param Collection|ResearchPlayer[] $researchPlayers
     */
    public function setResearchPlayers(Collection $researchPlayers)
    {
        $this->researchPlayers = $researchPlayers;
    }
}
