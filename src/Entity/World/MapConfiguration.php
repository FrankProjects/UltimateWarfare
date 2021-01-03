<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\World;

class MapConfiguration
{
    private int $size = 25;
    private float $persistence = 0.9;
    private int $seed = 1550441399;
    private int $waterLevel = 160;
    private int $beachLevel = 165;
    private int $forrestLevel = 230;

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

    public function getWaterLevel(): int
    {
        return $this->waterLevel;
    }

    public function setWaterLevel(int $waterLevel): void
    {
        $this->waterLevel = $waterLevel;
    }

    public function getBeachLevel(): int
    {
        return $this->beachLevel;
    }

    public function setBeachLevel(int $beachLevel): void
    {
        $this->beachLevel = $beachLevel;
    }

    public function getForrestLevel(): int
    {
        return $this->forrestLevel;
    }

    public function setForrestLevel(int $forrestLevel): void
    {
        $this->forrestLevel = $forrestLevel;
    }
}
