<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class Operation
{
    private ?int $id;
    private string $name;
    private string $image;
    private int $cost;
    private string $description;
    private bool $active = true;
    private float $difficulty = 0.5;
    private int $maxDistance;
    private Research $research;
    private GameUnit $gameUnit;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getDifficulty(): float
    {
        return $this->difficulty;
    }

    public function setDifficulty(float $difficulty): void
    {
        $this->difficulty = $difficulty;
    }

    public function getMaxDistance(): int
    {
        return $this->maxDistance;
    }

    public function setMaxDistance(int $maxDistance): void
    {
        $this->maxDistance = $maxDistance;
    }

    public function getResearch(): Research
    {
        return $this->research;
    }

    public function setResearch(Research $research): void
    {
        $this->research = $research;
    }

    public function getGameUnit(): GameUnit
    {
        return $this->gameUnit;
    }

    public function setGameUnit(GameUnit $gameUnit): void
    {
        $this->gameUnit = $gameUnit;
    }
}
