<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Report
 */
class Report
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $report;

    /**
     * @var Player
     */
    private $player;

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
     * Set type
     *
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
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
     * Set report
     *
     * @param string $report
     */
    public function setReport(string $report)
    {
        $this->report = $report;
    }

    /**
     * Get report
     *
     * @return string
     */
    public function getReport(): string
    {
        return $this->report;
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
     * @param Player $player
     * @param int $timestamp
     * @param int $type
     * @param string $message
     * @return Report
     */
    public static function create(Player $player, int $timestamp, int $type, string $message): Report
    {
        $report = new Report();
        $report->setPlayer($player);
        $report->setTimestamp($timestamp);
        $report->setType($type);
        $report->setReport($message);

        return $report;
    }
}
