<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class History
{
    private ?int $id;
    private int $worldId;
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
