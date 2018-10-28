<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * ResearchPlayer
 */
class ResearchPlayer
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var Research
     */
    private $research;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Set active
     *
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player)
    {
        $this->player = $player;
    }

    /**
     * @return Research
     */
    public function getResearch(): Research
    {
        return $this->research;
    }

    /**
     * @param Research $research
     */
    public function setResearch(Research $research)
    {
        $this->research = $research;
    }
}
