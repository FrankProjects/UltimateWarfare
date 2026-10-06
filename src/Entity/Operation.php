<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;

abstract readonly class Operation
{
    public const string COST_FLAT = 'flat';
    public const string COST_PER_UNIT = 'per_unit';
    public const string COST_PER_TARGET_REGION = 'per_target_region';

    /**
     * @param class-string<Research> $researchClass
     */
    public function __construct(
        private string $name,
        private string $image,
        private int $cost,
        private string $description,
        private bool $enabled,
        private float $difficulty,
        private int $maxDistance,
        private string $researchClass,
        private ?GameUnitEnum $gameUnit,
        private int $researchMinLevel = 1,
    ) {
    }

    abstract public function getSlug(): string;

    abstract public function getProcessorClass(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getDifficulty(): float
    {
        return $this->difficulty;
    }

    public function getMaxDistance(): int
    {
        return $this->maxDistance;
    }

    /**
     * @return class-string<Research>
     */
    public function getResearchClass(): string
    {
        return $this->researchClass;
    }

    public function getResearchSlug(): string
    {
        return (new $this->researchClass())->getSlug();
    }

    public function getResearchName(): string
    {
        return (new $this->researchClass())->getName();
    }

    public function getGameUnit(): ?GameUnitEnum
    {
        return $this->gameUnit;
    }

    public function getResearchMinLevel(): int
    {
        return $this->researchMinLevel;
    }

    public function hasCooldown(): bool
    {
        return false;
    }

    public function getCostType(): string
    {
        return $this->gameUnit === null ? self::COST_FLAT : self::COST_PER_UNIT;
    }

    public function calculateCost(WorldRegion $targetRegion, int $amount): int
    {
        return $this->cost * max(1, $amount);
    }
}
