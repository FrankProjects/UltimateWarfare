<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * HistoryPlayer
 */
class HistoryPlayer
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
    private $round;

    /**
     * @var string
     */
    private $playerName;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $fedId;

    /**
     * @var int
     */
    private $regions;

    /**
     * @var int
     */
    private $networth;

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
     * Set worldId
     *
     * @param int $worldId
     */
    public function setWorldId(int $worldId)
    {
        $this->worldId = $worldId;
    }

    /**
     * Get worldId
     *
     * @return int
     */
    public function getWorldId(): int
    {
        return $this->worldId;
    }

    /**
     * Set round
     *
     * @param int $round
     */
    public function setRound(int $round)
    {
        $this->round = $round;
    }

    /**
     * Get round
     *
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * Set playerName
     *
     * @param string $playerName
     */
    public function setPlayerName(string $playerName)
    {
        $this->playerName = $playerName;
    }

    /**
     * Get playerName
     *
     * @return string
     */
    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    /**
     * Set userId
     *
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set fedId
     *
     * @param int $fedId
     */
    public function setFedId(int $fedId)
    {
        $this->fedId = $fedId;
    }

    /**
     * Get fedId
     *
     * @return int
     */
    public function getFedId(): int
    {
        return $this->fedId;
    }

    /**
     * Set regions
     *
     * @param int $regions
     */
    public function setRegions(int $regions)
    {
        $this->regions = $regions;
    }

    /**
     * Get regions
     *
     * @return int
     */
    public function getRegions(): int
    {
        return $this->regions;
    }

    /**
     * Set networth
     *
     * @param int $networth
     */
    public function setNetworth(int $networth)
    {
        $this->networth = $networth;
    }

    /**
     * Get networth
     *
     * @return int
     */
    public function getNetworth(): int
    {
        return $this->networth;
    }
}
