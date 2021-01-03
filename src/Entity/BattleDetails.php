<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * XXX TODO: Fix model to use correct relationships
 */
class BattleDetails
{
    private ?int $id;
    private int $worldId;
    private int $attacker;
    private int $defender;
    private string $battleLog;
    private bool $type;
    private int $timestamp;

    public function getId(): int
    {
        return $this->id;
    }

    public function setWorldId(int $worldId): void
    {
        $this->worldId = $worldId;
    }

    public function getWorldId(): int
    {
        return $this->worldId;
    }

    public function setAttacker(int $attacker): void
    {
        $this->attacker = $attacker;
    }

    public function getAttacker(): int
    {
        return $this->attacker;
    }

    public function setDefender(int $defender): void
    {
        $this->defender = $defender;
    }

    public function getDefender(): int
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

    public function setType(bool $type)
    {
        $this->type = $type;
    }

    public function getType(): bool
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
}
