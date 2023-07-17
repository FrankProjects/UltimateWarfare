<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class ResearchPlayer
{
    private ?int $id;
    private int $timestamp;
    private bool $active = false;
    private Player $player;
    private Research $research;

    public function setId(?int $id): void
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

    public function getResearch(): Research
    {
        return $this->research;
    }

    public function setResearch(Research $research): void
    {
        $this->research = $research;
    }
}
