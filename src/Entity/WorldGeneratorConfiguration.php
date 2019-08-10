<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class WorldGeneratorConfiguration
{
    const DEFAULT_SIZE = 125;
    const DEFAULT_PERSISTENCE = 0.9;
    const DEFAULT_SEED = 1550441399;
    const DEFAULT_WATER_LEVEL = 160;
    const DEFAULT_BEACH_LEVEL = 165;
    const DEFAULT_FORREST_LEVEL = 230;

    /**
     * @var int
     */
    private $size;

    /**
     * @var float
     */
    private $persistence;

    /**
     * @var int
     */
    private $seed;

    /**
     * @var int
     */
    private $waterLevel;

    /**
     * @var int
     */
    private $beachLevel;

    /**
     * @var int
     */
    private $forrestLevel;

    /**
     * WorldGeneratorConfiguration constructor.
     */
    public function __construct()
    {
        $this->setSize(self::DEFAULT_SIZE);
        $this->setPersistence(self::DEFAULT_PERSISTENCE);
        $this->setSeed(self::DEFAULT_SEED);
        $this->setWaterLevel(self::DEFAULT_WATER_LEVEL);
        $this->setBeachLevel(self::DEFAULT_BEACH_LEVEL);
        $this->setForrestLevel(self::DEFAULT_FORREST_LEVEL);
    }

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
