<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\World;

class MapConfiguration
{
    /**
     * @var int
     */
    private $size = 25;

    /**
     * @var float
     */
    private $persistence = 0.9;

    /**
     * @var int
     */
    private $seed = 1550441399;

    /**
     * @var int
     */
    private $waterLevel = 160;

    /**
     * @var int
     */
    private $beachLevel = 165;

    /**
     * @var int
     */
    private $forrestLevel = 230;

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return float
     */
    public function getPersistence(): float
    {
        return $this->persistence;
    }

    /**
     * @param float $persistence
     */
    public function setPersistence(float $persistence): void
    {
        $this->persistence = $persistence;
    }

    /**
     * @return int
     */
    public function getSeed(): int
    {
        return $this->seed;
    }

    /**
     * @param int $seed
     */
    public function setSeed(int $seed): void
    {
        $this->seed = $seed;
    }

    /**
     * @return int
     */
    public function getWaterLevel(): int
    {
        return $this->waterLevel;
    }

    /**
     * @param int $waterLevel
     */
    public function setWaterLevel(int $waterLevel): void
    {
        $this->waterLevel = $waterLevel;
    }

    /**
     * @return int
     */
    public function getBeachLevel(): int
    {
        return $this->beachLevel;
    }

    /**
     * @param int $beachLevel
     */
    public function setBeachLevel(int $beachLevel): void
    {
        $this->beachLevel = $beachLevel;
    }

    /**
     * @return int
     */
    public function getForrestLevel(): int
    {
        return $this->forrestLevel;
    }

    /**
     * @param int $forrestLevel
     */
    public function setForrestLevel(int $forrestLevel): void
    {
        $this->forrestLevel = $forrestLevel;
    }
}
