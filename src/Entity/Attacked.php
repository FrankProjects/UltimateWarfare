<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Attacked
 */
class Attacked
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $attacker;

    /**
     * @var int
     */
    private $defender;

    /**
     * @var int
     */
    private $timestamp;


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
     * Set attacker
     *
     * @param int $attacker
     *
     * @return Attacked
     */
    public function setAttacker($attacker)
    {
        $this->attacker = $attacker;

        return $this;
    }

    /**
     * Get attacker
     *
     * @return int
     */
    public function getAttacker()
    {
        return $this->attacker;
    }

    /**
     * Set defender
     *
     * @param int $defender
     *
     * @return Attacked
     */
    public function setDefender($defender)
    {
        $this->defender = $defender;

        return $this;
    }

    /**
     * Get defender
     *
     * @return int
     */
    public function getDefender()
    {
        return $this->defender;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     *
     * @return Attacked
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
