<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Report
 */
class Report
{
    const TYPE_ATTACKED = 1;
    const TYPE_GENERAL = 2;
    const TYPE_MARKET = 4;
    const TYPE_AID = 5;

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
    public static function createForPlayer(Player $player, int $timestamp, int $type, string $message): Report
    {
        $report = new Report();
        $report->setPlayer($player);
        $report->setTimestamp($timestamp);
        $report->setType($type);
        $report->setReport($message);

        return $report;
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getReportSubject(int $type): string
    {
        if ($type == self::TYPE_ATTACKED) {
            return 'Battle reports';
        }

        if ($type == self::TYPE_GENERAL) {
            return 'General reports';
        }

        if ($type == self::TYPE_MARKET) {
            return 'Market reports';
        }

        if ($type == self::TYPE_AID) {
            return 'Aid reports';
        }

        return 'All reports';
    }
}
