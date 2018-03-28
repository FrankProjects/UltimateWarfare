<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * MarketItemType
 */
class MarketItemType
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
     * MarketItemType constructor.
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
}
