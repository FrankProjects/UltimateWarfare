<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * XXX TODO: Remove round and set world name
 */
class History
{
    private ?int $id;
    private int $worldId;
    private int $round;
    private string $name;
    private int $endDate;

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

    public function setRound(int $round): void
    {
        $this->round = $round;
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEndDate(int $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getEndDate(): int
    {
        return $this->endDate;
    }
}
