<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use InvalidArgumentException;

abstract readonly class Research
{
    /**
     * @param array<int,int>                                  $costPerLevel          1-indexed map level => cost
     * @param array<int,int>                                  $timestampPerLevel     1-indexed map level => seconds
     * @param array<int, array<class-string<Research>, int>>  $prerequisitesPerLevel 1-indexed; for each level, a map
     *                                                                               of prerequisite research class to
     *                                                                               its required minimum level. The
     *                                                                               implicit "previous level of this
     *                                                                               same research" prerequisite is
     *                                                                               enforced by sequential progression.
     */
    public function __construct(
        private string $name,
        private string $image,
        private string $description,
        private bool $enabled,
        private array $costPerLevel,
        private array $timestampPerLevel,
        private array $prerequisitesPerLevel = [],
    ) {
    }

    abstract public function getSlug(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getMaxLevel(): int
    {
        return count($this->costPerLevel);
    }

    public function getCost(int $level): int
    {
        $this->assertValidLevel($level);
        return $this->costPerLevel[$level];
    }

    public function getTimestamp(int $level): int
    {
        $this->assertValidLevel($level);
        return $this->timestampPerLevel[$level];
    }

    /**
     * @return array<class-string<Research>, int>
     */
    public function getPrerequisites(int $level): array
    {
        $this->assertValidLevel($level);
        return $this->prerequisitesPerLevel[$level] ?? [];
    }

    /**
     * @return array<array{slug: string, name: string, minLevel: int}>
     */
    public function getPrerequisiteDescriptions(int $level): array
    {
        $descriptions = [];
        foreach ($this->getPrerequisites($level) as $prerequisiteClass => $minLevel) {
            $prerequisite = new $prerequisiteClass();
            $descriptions[] = [
                'slug' => $prerequisite->getSlug(),
                'name' => $prerequisite->getName(),
                'minLevel' => $minLevel,
            ];
        }

        return $descriptions;
    }

    private function assertValidLevel(int $level): void
    {
        if (!isset($this->costPerLevel[$level]) || !isset($this->timestampPerLevel[$level])) {
            throw new InvalidArgumentException(
                sprintf('Level %d is not defined for research %s', $level, $this->getSlug())
            );
        }
    }
}
