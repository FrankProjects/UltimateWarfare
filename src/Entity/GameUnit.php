<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameResources\Cost;
use FrankProjects\UltimateWarfare\Entity\GameResources\Income;
use FrankProjects\UltimateWarfare\Entity\GameResources\Upkeep;

abstract class GameUnit
{
    /**
     * @param class-string<Research>|null $researchClass
     */
    public function __construct(
        private readonly string $name,
        private readonly string $nameMulti,
        private readonly string $rowName,
        private readonly string $image,
        private readonly int $netWorth,
        private readonly int $timestamp,
        private readonly string $description,
        private readonly GameUnitCategory $gameUnitCategory,
        private readonly ?string $behaviorClass,
        private readonly Cost $cost,
        private readonly Income $income,
        private readonly Upkeep $upkeep,
        private readonly BattleStats $battleStats,
        private readonly ?string $researchClass = null,
    ) {
    }

    abstract public function getGameUnitEnum(): GameUnitEnum;

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameMulti(): string
    {
        return $this->nameMulti;
    }

    public function getRowName(): string
    {
        return $this->rowName;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getNetWorth(): int
    {
        return $this->netWorth;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGameUnitCategory(): GameUnitCategory
    {
        return $this->gameUnitCategory;
    }

    public function getBehaviorClass(): ?string
    {
        return $this->behaviorClass;
    }

    public function getCost(): Cost
    {
        return $this->cost;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getUpkeep(): Upkeep
    {
        return $this->upkeep;
    }

    public function getBattleStats(): BattleStats
    {
        return $this->battleStats;
    }

    /**
     * @return class-string<Research>|null
     */
    public function getResearchClass(): ?string
    {
        return $this->researchClass;
    }

    public function getResearchSlug(): ?string
    {
        if ($this->researchClass === null) {
            return null;
        }

        return (new $this->researchClass())->getSlug();
    }

    public function getResearchName(): ?string
    {
        if ($this->researchClass === null) {
            return null;
        }

        return (new $this->researchClass())->getName();
    }
}
