<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Player;

class Resources
{
    /**
     * @var int
     */
    private $cash;

    /**
     * @var int
     */
    private $steel;

    /**
     * @var int
     */
    private $wood;

    /**
     * @var int
     */
    private $food;

    /**
     * @var int
     */
    private $incomeCash = 0;

    /**
     * @var int
     */
    private $incomeFood = 0;

    /**
     * @var int
     */
    private $incomeWood = 0;

    /**
     * @var int
     */
    private $incomeSteel = 0;

    /**
     * @var int
     */
    private $upkeepCash = 0;

    /**
     * @var int
     */
    private $upkeepFood = 0;

    /**
     * @var int
     */
    private $upkeepWood = 0;

    /**
     * @var int
     */
    private $upkeepSteel = 0;

    /**
     * Set cash
     *
     * @param int $cash
     */
    public function setCash(int $cash)
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
    public function setSteel(int $steel)
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
    public function setWood(int $wood)
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
    public function setFood(int $food)
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

    /**
     * Set incomeCash
     *
     * @param int $incomeCash
     */
    public function setIncomeCash(int $incomeCash)
    {
        $this->incomeCash = $incomeCash;
    }

    /**
     * Get incomeCash
     *
     * @return int
     */
    public function getIncomeCash(): int
    {
        return $this->incomeCash;
    }

    /**
     * Set incomeFood
     *
     * @param int $incomeFood
     */
    public function setIncomeFood(int $incomeFood)
    {
        $this->incomeFood = $incomeFood;
    }

    /**
     * Get incomeFood
     *
     * @return int
     */
    public function getIncomeFood(): int
    {
        return $this->incomeFood;
    }

    /**
     * Set incomeWood
     *
     * @param int $incomeWood
     */
    public function setIncomeWood(int $incomeWood)
    {
        $this->incomeWood = $incomeWood;
    }

    /**
     * Get incomeWood
     *
     * @return int
     */
    public function getIncomeWood(): int
    {
        return $this->incomeWood;
    }

    /**
     * Set incomeSteel
     *
     * @param int $incomeSteel
     */
    public function setIncomeSteel(int $incomeSteel)
    {
        $this->incomeSteel = $incomeSteel;
    }

    /**
     * Get incomeSteel
     *
     * @return int
     */
    public function getIncomeSteel(): int
    {
        return $this->incomeSteel;
    }

    /**
     * Set upkeepCash
     *
     * @param int $upkeepCash
     */
    public function setUpkeepCash(int $upkeepCash)
    {
        $this->upkeepCash = $upkeepCash;
    }

    /**
     * Get upkeepCash
     *
     * @return int
     */
    public function getUpkeepCash(): int
    {
        return $this->upkeepCash;
    }

    /**
     * Set upkeepFood
     *
     * @param int $upkeepFood
     */
    public function setUpkeepFood(int $upkeepFood)
    {
        $this->upkeepFood = $upkeepFood;
    }

    /**
     * Get upkeepFood
     *
     * @return int
     */
    public function getUpkeepFood(): int
    {
        return $this->upkeepFood;
    }

    /**
     * Set upkeepWood
     *
     * @param int $upkeepWood
     */
    public function setUpkeepWood(int $upkeepWood)
    {
        $this->upkeepWood = $upkeepWood;
    }

    /**
     * Get upkeepWood
     *
     * @return int
     */
    public function getUpkeepWood(): int
    {
        return $this->upkeepWood;
    }

    /**
     * Set upkeepSteel
     *
     * @param int $upkeepSteel
     */
    public function setUpkeepSteel(int $upkeepSteel)
    {
        $this->upkeepSteel = $upkeepSteel;
    }

    /**
     * Get upkeepSteel
     *
     * @return int
     */
    public function getUpkeepSteel(): int
    {
        return $this->upkeepSteel;
    }
}
