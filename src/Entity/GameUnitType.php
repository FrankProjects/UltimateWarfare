<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * GameUnitType
 */
class GameUnitType
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
    private $imageDir;

    /**
     * @var Collection|GameUnit[]
     */
    private $gameUnits = [];

    /**
     * GameUnit constructor.
     */
    public function __construct()
    {
        $this->gameUnits = new ArrayCollection();
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
     * Set imageDir
     *
     * @param string $imageDir
     */
    public function setImageDir(string $imageDir): void
    {
        $this->imageDir = $imageDir;
    }

    /**
     * Get imageDir
     *
     * @return string
     */
    public function getImageDir(): string
    {
        return $this->imageDir;
    }

    /**
     * @return Collection
     */
    public function getGameUnits(): Collection
    {
        return $this->gameUnits;
    }

    /**
     * @param Collection $gameUnits
     */
    public function setGameUnits(Collection $gameUnits): void
    {
        $this->gameUnits = $gameUnits;
    }
}
