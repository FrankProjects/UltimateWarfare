<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * MapDesign
 */
class MapDesign
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
    private $url;

    /**
     * @var string
     */
    private $imageDir;

    /**
     * @var Collection|User[]
     */
    private $users = [];

    /**
     * MapDesign constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
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
     *
     * @return MapDesign
     */
    public function setName(string $name): MapDesign
    {
        $this->name = $name;

        return $this;
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
     * Set url
     *
     * @param string $url
     *
     * @return MapDesign
     */
    public function setUrl(string $url): MapDesign
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set imageDir
     *
     * @param string $imageDir
     *
     * @return MapDesign
     */
    public function setImageDir(string $imageDir): MapDesign
    {
        $this->imageDir = $imageDir;

        return $this;
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
}
