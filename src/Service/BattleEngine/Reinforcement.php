<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;

/**
 * Units a neighbouring region sends through one of its transport buildings to help defend a battle.
 * The battle unit only exists during the battle; losses are taken from the source unit afterwards.
 */
final class Reinforcement
{
    private WorldRegionStackableUnit $sourceUnit;
    private WorldRegionStackableUnit $battleUnit;
    private GameUnitEnum $building;
    private int $amount;

    public function __construct(
        WorldRegionStackableUnit $sourceUnit,
        GameUnitEnum $building,
        int $amount
    ) {
        $this->sourceUnit = $sourceUnit;
        $this->building = $building;
        $this->amount = $amount;

        // Not created through WorldRegionStackableUnit::create(), so it is never added to the region or persisted
        $this->battleUnit = new WorldRegionStackableUnit();
        $this->battleUnit->setWorldRegion($sourceUnit->getWorldRegion());
        $this->battleUnit->setGameUnit($sourceUnit->getGameUnit());
        $this->battleUnit->setAmount($amount);
    }

    public function getSourceUnit(): WorldRegionStackableUnit
    {
        return $this->sourceUnit;
    }

    public function getBattleUnit(): WorldRegionStackableUnit
    {
        return $this->battleUnit;
    }

    public function getBuilding(): GameUnitEnum
    {
        return $this->building;
    }

    /**
     * The amount sent into battle; the battle unit's amount drops with its casualties
     */
    public function getAmount(): int
    {
        return $this->amount;
    }
}
