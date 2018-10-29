<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * BattleDetails
 */
class BattleDetails
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $worldId;

    /**
     * @var int
     */
    private $attacker;

    /**
     * @var int
     */
    private $defender;

    /**
     * @var string
     */
    private $battleLog;

    /**
     * @var bool
     */
    private $type;

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
     * Set worldId
     *
     * @param int $worldId
     *
     * @return BattleDetails
     */
    public function setWorldId($worldId)
    {
        $this->worldId = $worldId;

        return $this;
    }

    /**
     * Get worldId
     *
     * @return int
     */
    public function getWorldId()
    {
        return $this->worldId;
    }

    /**
     * Set attacker
     *
     * @param int $attacker
     *
     * @return BattleDetails
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
     * @return BattleDetails
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
     * Set battleLog
     *
     * @param string $battleLog
     *
     * @return BattleDetails
     */
    public function setBattleLog($battleLog)
    {
        $this->battleLog = $battleLog;

        return $this;
    }

    /**
     * Get battleLog
     *
     * @return string
     */
    public function getBattleLog()
    {
        return $this->battleLog;
    }

    /**
     * Set type
     *
     * @param bool $type
     *
     * @return BattleDetails
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     *
     * @return BattleDetails
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
