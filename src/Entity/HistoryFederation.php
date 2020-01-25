<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * XXX TODO: Remove round from model
 */
class HistoryFederation
{
    private ?int $id;
    private int $worldId;
    private int $round;
    private string $federation;
    private int $fedId;
    private int $regions;
    private int $networth;

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

    public function setFederation(string $federation): void
    {
        $this->federation = $federation;
    }

    public function getFederation(): string
    {
        return $this->federation;
    }

    public function setFedId(int $fedId): void
    {
        $this->fedId = $fedId;
    }

    public function getFedId(): int
    {
        return $this->fedId;
    }

    public function setRegions(int $regions): void
    {
        $this->regions = $regions;
    }

    public function getRegions(): int
    {
        return $this->regions;
    }

    public function setNetworth(int $networth): void
    {
        $this->networth = $networth;
    }

    public function getNetworth(): int
    {
        return $this->networth;
    }
}
