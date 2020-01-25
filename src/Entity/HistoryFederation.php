<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class HistoryFederation
{
    private ?int $id;
    private int $worldId;
    private string $federation;
    private int $federationId;
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

    public function setFederation(string $federation): void
    {
        $this->federation = $federation;
    }

    public function getFederation(): string
    {
        return $this->federation;
    }

    public function getFederationId(): int
    {
        return $this->federationId;
    }

    public function setFederationId(int $federationId): void
    {
        $this->federationId = $federationId;
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
