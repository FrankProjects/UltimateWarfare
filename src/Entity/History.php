<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * History
 */
class History
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
    private $name;

    /**
     * @var int
     */
    private $endDate;

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
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set endDate
     *
     * @param int $endDate
     */
    public function setEndDate(int $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return int
     */
    public function getEndDate(): int
    {
        return $this->endDate;
    }
}
