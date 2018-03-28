<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Operation
 */
class Operation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $needs;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $pic;

    /**
     * @var int
     */
    private $unitId;

    /**
     * @var int
     */
    private $cost;

    /**
     * @var int
     */
    private $tick;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var float
     */
    private $difficulty = 0.5;

    /**
     * @var int
     */
    private $maxDistance;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set needs
     *
     * @param int $needs
     *
     * @return Operation
     */
    public function setNeeds($needs)
    {
        $this->needs = $needs;

        return $this;
    }

    /**
     * Get needs
     *
     * @return int
     */
    public function getNeeds()
    {
        return $this->needs;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Operation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set pic
     *
     * @param string $pic
     *
     * @return Operation
     */
    public function setPic($pic)
    {
        $this->pic = $pic;

        return $this;
    }

    /**
     * Get pic
     *
     * @return string
     */
    public function getPic()
    {
        return $this->pic;
    }

    /**
     * Set unitId
     *
     * @param int $unitId
     *
     * @return Operation
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;

        return $this;
    }

    /**
     * Get unitId
     *
     * @return int
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * Set cost
     *
     * @param int $cost
     *
     * @return Operation
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set tick
     *
     * @param int $tick
     *
     * @return Operation
     */
    public function setTick($tick)
    {
        $this->tick = $tick;

        return $this;
    }

    /**
     * Get tick
     *
     * @return int
     */
    public function getTick()
    {
        return $this->tick;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Operation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set active
     *
     * @param bool $active
     *
     * @return Operation
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set difficulty
     *
     * @param float $difficulty
     *
     * @return Operation
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    /**
     * Get difficulty
     *
     * @return float
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * Set maxDistance
     *
     * @param int $maxDistance
     *
     * @return Operation
     */
    public function setMaxDistance($maxDistance)
    {
        $this->maxDistance = $maxDistance;

        return $this;
    }

    /**
     * Get maxDistance
     *
     * @return int
     */
    public function getMaxDistance()
    {
        return $this->maxDistance;
    }
}
