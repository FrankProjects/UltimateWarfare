<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

abstract class AbstractGameResources
{
    /**
     * @var int
     */
    protected $cash = 0;

    /**
     * @var int
     */
    protected $food = 0;

    /**
     * @var int
     */
    protected $wood = 0;

    /**
     * @var int
     */
    protected $steel = 0;

    /**
     * Set Cash
     *
     * @param int $cash
     */
    public function setCash(int $cash): void
    {
        $this->cash = $cash;
    }

    /**
     * Get Cash
     *
     * @return int
     */
    public function getCash(): int
    {
        return $this->cash;
    }

    /**
     * Set Food
     *
     * @param int $food
     */
    public function setFood(int $food): void
    {
        $this->food = $food;
    }

    /**
     * Get Food
     *
     * @return int
     */
    public function getFood(): int
    {
        return $this->food;
    }

    /**
     * Set Wood
     *
     * @param int $wood
     */
    public function setWood(int $wood): void
    {
        $this->wood = $wood;
    }

    /**
     * Get Wood
     *
     * @return int
     */
    public function getWood(): int
    {
        return $this->wood;
    }

    /**
     * Set Steel
     *
     * @param int $steel
     */
    public function setSteel(int $steel): void
    {
        $this->steel = $steel;
    }

    /**
     * Get Steel
     *
     * @return int
     */
    public function getSteel(): int
    {
        return $this->steel;
    }

    /**
     * @param int $cash
     */
    public function addCash(int $cash): void
    {
        $this->cash += $cash;
    }

    /**
     * @param int $food
     */
    public function addFood(int $food): void
    {
        $this->food += $food;
    }

    /**
     * @param int $wood
     */
    public function addWood(int $wood): void
    {
        $this->wood += $wood;
    }

    /**
     * @param int $steel
     */
    public function addSteel(int $steel): void
    {
        $this->steel += $steel;
    }

    /**
     * @param AbstractGameResources $abstractGameResources
     * @return bool
     */
    public function equals(AbstractGameResources $abstractGameResources): bool
    {
        if (
            $abstractGameResources->cash === $this->cash &&
            $abstractGameResources->food === $this->food &&
            $abstractGameResources->steel === $this->steel &&
            $abstractGameResources->wood === $this->wood
        ) {
            return true;
        }
        return false;
    }
}
