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

    /**
     * Lost units per side keyed by game unit enum value, null for battles from before losses were recorded
     *
     * @var array<string, array<int, int>>|null
     */
    private ?array $lostUnits = null;

    /**
     * @param array<string> $battleLog
     * @param array<string, array<int, int>> $lostUnits
     */
    public static function create(
        World $world,
        Player $attacker,
        Player $defender,
        array $battleLog,
        bool $attackerWon,
        int $timestamp,
        array $lostUnits = []
    ): self {
        $battleSummary = new self();
        $battleSummary->world = $world;
        $battleSummary->attacker = $attacker;
        $battleSummary->defender = $defender;
        $battleSummary->battleLog = implode("\n", $battleLog);
        $battleSummary->attackerWon = $attackerWon;
        $battleSummary->timestamp = $timestamp;
        $battleSummary->lostUnits = $lostUnits;

        return $battleSummary;
    }

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

    /**
     * @return array<string>
     */
    public function getBattleLogLines(): array
    {
        return explode("\n", $this->battleLog);
    }

    public function isParticipant(Player $player): bool
    {
        return $this->attacker->getId() === $player->getId() || $this->defender->getId() === $player->getId();
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

    /**
     * @return array<string, array<int, int>>|null
     */
    public function getLostUnits(): ?array
    {
        return $this->lostUnits;
    }
}
