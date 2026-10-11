<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class BattleSummary
{
    private int $id;
    private World $world;
    private Player $attacker;
    private Player $defender;
    private string $battleLog;
    private bool $attackerWon;
    private int $timestamp;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setAttacker(Player $attacker): void
    {
        $this->attacker = $attacker;
    }

    public function getAttacker(): Player
    {
        return $this->attacker;
    }

    public function setDefender(Player $defender): void
    {
        $this->defender = $defender;
    }

    public function getDefender(): Player
    {
        return $this->defender;
    }

    public function setBattleLog(string $battleLog): void
    {
        $this->battleLog = $battleLog;
    }

    public function getBattleLog(): string
    {
        return $this->battleLog;
    }

    public function setAttackerWon(bool $attackerWon): void
    {
        $this->attackerWon = $attackerWon;
    }

    public function hasAttackerWon(): bool
    {
        return $this->attackerWon;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
