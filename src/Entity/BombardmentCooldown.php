<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class BombardmentCooldown
{
    private int $id;
    private string $operationSlug;
    private World $world;
    private WorldRegion $worldRegion;
    private WorldRegion $targetWorldRegion;
    private int $cooldownUntil;

    public static function create(
        string $operationSlug,
        WorldRegion $worldRegion,
        WorldRegion $targetWorldRegion,
        int $cooldownUntil
    ): self {
        $cooldown = new self();
        $cooldown->operationSlug = $operationSlug;
        $cooldown->world = $worldRegion->getWorld();
        $cooldown->worldRegion = $worldRegion;
        $cooldown->targetWorldRegion = $targetWorldRegion;
        $cooldown->cooldownUntil = $cooldownUntil;

        return $cooldown;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOperationSlug(): string
    {
        return $this->operationSlug;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function getWorldRegion(): WorldRegion
    {
        return $this->worldRegion;
    }

    public function getTargetWorldRegion(): WorldRegion
    {
        return $this->targetWorldRegion;
    }

    public function getCooldownUntil(): int
    {
        return $this->cooldownUntil;
    }
}
