<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\World;

class MapConfiguration
{
    private int $size = 25;
    private float $persistence = 0.9;
    private int $seed = 1550441399;
    private int $deepWaterLevel = 130;
    private int $waterLevel = 150;
    private int $shallowWaterLevel = 160;
    private int $sandLevel = 168;
    private int $grasslandLevel = 200;
    private int $forestLevel = 220;
    private int $hillsLevel = 225;

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getPersistence(): float
    {
        return $this->persistence;
    }

    public function setPersistence(float $persistence): void
    {
        $this->persistence = $persistence;
    }

    public function getSeed(): int
    {
        return $this->seed;
    }

    public function setSeed(int $seed): void
    {
        $this->seed = $seed;
    }

    public function getDeepWaterLevel(): int
    {
        return $this->deepWaterLevel;
    }

    public function setDeepWaterLevel(int $deepWaterLevel): void
    {
        $this->deepWaterLevel = $deepWaterLevel;
    }

    public function getWaterLevel(): int
    {
        return $this->waterLevel;
    }

    public function setWaterLevel(int $waterLevel): void
    {
        $this->waterLevel = $waterLevel;
    }

    public function getShallowWaterLevel(): int
    {
        return $this->shallowWaterLevel;
    }

    public function setShallowWaterLevel(int $shallowWaterLevel): void
    {
        $this->shallowWaterLevel = $shallowWaterLevel;
    }

    public function getSandLevel(): int
    {
        return $this->sandLevel;
    }

    public function setSandLevel(int $sandLevel): void
    {
        $this->sandLevel = $sandLevel;
    }

    public function getGrasslandLevel(): int
    {
        return $this->grasslandLevel;
    }

    public function setGrasslandLevel(int $grasslandLevel): void
    {
        $this->grasslandLevel = $grasslandLevel;
    }

    public function getForestLevel(): int
    {
        return $this->forestLevel;
    }

    public function setForestLevel(int $forestLevel): void
    {
        $this->forestLevel = $forestLevel;
    }

    public function getHillsLevel(): int
    {
        return $this->hillsLevel;
    }

    public function setHillsLevel(int $hillsLevel): void
    {
        $this->hillsLevel = $hillsLevel;
    }
}
