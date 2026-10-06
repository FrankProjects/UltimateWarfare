<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class ResearchPlayer
{
    private int $id;
    private int $timestamp;
    private bool $active = false;
    private Player $player;
    private string $researchSlug;
    private int $level = 1;
    private int $completionTimestamp = 0;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getResearchSlug(): string
    {
        return $this->researchSlug;
    }

    public function setResearchSlug(string $researchSlug): void
    {
        $this->researchSlug = $researchSlug;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getCompletionTimestamp(): int
    {
        return $this->completionTimestamp;
    }

    public function setCompletionTimestamp(int $completionTimestamp): void
    {
        $this->completionTimestamp = $completionTimestamp;
    }
}
