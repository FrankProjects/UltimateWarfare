<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * GameResource
 */
class GameResource
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
     * @var Collection|MarketItem[]
     */
    private $marketItems = [];

    /**
     * GameResource constructor.
     */
    public function __construct()
    {
        $this->marketItems = new ArrayCollection();
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
     * @return Collection|MarketItem[]
     */
    public function getMarketItems(): Collection
    {
        return $this->marketItems;
    }

    /**
     * @param array $marketItems
     */
    public function setMarketItems(array $marketItems): void
    {
        $this->marketItems = $marketItems;
    }
}
