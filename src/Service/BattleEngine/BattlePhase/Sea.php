<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

/**
 * Before the speed tiers, attacking Mine Countermeasures Ships clear mines and the Sea Mines detonate
 * on the attacking ships.
 */
final class Sea extends BattlePhase
{
    private const int MINES_CLEARED_PER_COUNTERMEASURES_SHIP = 25;

    protected function beforeSpeedTiers(): void
    {
        $this->sweepMines(
            GameUnitEnum::SEA_MINE,
            GameUnitEnum::MINE_COUNTERMEASURES_SHIP,
            self::MINES_CLEARED_PER_COUNTERMEASURES_SHIP
        );
        $this->detonateMines(GameUnitEnum::SEA_MINE, GameUnitCategory::NAVAL_UNITS);
    }

    /**
     * Sea Mines only strike once, when they detonate before the speed tiers
     */
    protected function firesInSpeedTiers(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): bool
    {
        return $defenderGameUnit->getGameUnit() !== GameUnitEnum::SEA_MINE;
    }
}
