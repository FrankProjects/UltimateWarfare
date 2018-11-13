<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Federation;

class Resources
{
    /**
     * @var int
     */
    private $cash = 0;

    /**
     * @var int
     */
    private $steel = 0;

    /**
     * @var int
     */
    private $wood = 0;

    /**
     * @var int
     */
    private $food = 0;

    /**
     * Set cash
     *
     * @param int $cash
     */
    public function setCash(int $cash): void
    {
        $this->cash = $cash;
    }

    /**
     * Get cash
     *
     * @return int
     */
    public function getCash(): int
    {
        return $this->cash;
    }

    /**
     * Set steel
     *
     * @param int $steel
     */
    public function setSteel(int $steel): void
    {
        $this->steel = $steel;
    }

    /**
     * Get steel
     *
     * @return int
     */
    public function getSteel(): int
    {
        return $this->steel;
    }

    /**
     * Set wood
     *
     * @param int $wood
     */
    public function setWood(int $wood): void
    {
        $this->wood = $wood;
    }

    /**
     * Get wood
     *
     * @return int
     */
    public function getWood(): int
    {
        return $this->wood;
    }

    /**
     * Set food
     *
     * @param int $food
     */
    public function setFood(int $food): void
    {
        $this->food = $food;
    }

    /**
     * Get food
     *
     * @return int
     */
    public function getFood(): int
    {
        return $this->food;
    }
}
