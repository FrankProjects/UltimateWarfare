<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class Report
{
    public const int TYPE_ATTACKED = 1;
    public const int TYPE_GENERAL = 2;
    public const int TYPE_MARKET = 4;
    public const int TYPE_AID = 5;

    private int $id;
    private int $type;
    private int $timestamp;
    private string $report;
    private Player $player;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setReport(string $report): void
    {
        $this->report = $report;
    }

    public function getReport(): string
    {
        return $this->report;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public static function createForPlayer(Player $player, int $timestamp, int $type, string $message): Report
    {
        $report = new Report();
        $report->setPlayer($player);
        $report->setTimestamp($timestamp);
        $report->setType($type);
        $report->setReport($message);

        return $report;
    }

    public static function getReportSubject(int $type): string
    {
        if ($type === self::TYPE_ATTACKED) {
            return 'Battle reports';
        }

        if ($type === self::TYPE_GENERAL) {
            return 'General reports';
        }

        if ($type === self::TYPE_MARKET) {
            return 'Market reports';
        }

        if ($type === self::TYPE_AID) {
            return 'Aid reports';
        }

        return 'All reports';
    }
}
