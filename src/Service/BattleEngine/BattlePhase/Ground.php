<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

/**
 * Before the speed tiers, attacking Mine Sweepers clear mines, the Mine Field detonates on the attacking
 * troops and attacking Artillery bombard the Bunker. During the speed tiers the Bunker shelters defending troops.
 */
final class Ground extends BattlePhase
{
    private const int MINES_CLEARED_PER_SWEEPER = 25;
    private const int BUNKER_CAPACITY_PER_LEVEL = 100;
    private const float BUNKER_DAMAGE_REDUCTION = 0.5;

    private bool $bunkerShelterLogged = false;

    protected function beforeSpeedTiers(): void
    {
        $this->sweepMines(GameUnitEnum::MINE_FIELD, GameUnitEnum::MINE_SWEEPER, self::MINES_CLEARED_PER_SWEEPER);
        $this->detonateMines(GameUnitEnum::MINE_FIELD, GameUnitCategory::TROOPS);
        $this->bombardBunker();
    }

    /**
     * Mine Fields only strike once, when they detonate before the speed tiers
     */
    protected function firesInSpeedTiers(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): bool
    {
        return $defenderGameUnit->getGameUnit() !== GameUnitEnum::MINE_FIELD;
    }

    /**
     * Defending troops sheltered in the Bunker take reduced damage, up to the Bunker's capacity
     */
    protected function getIncomingPower(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit,
        int $power,
        string $action
    ): int {
        if ($action !== 'defending' || !$this->hasGameUnitCategory($gameUnit, GameUnitCategory::TROOPS)) {
            return $power;
        }

        $bunker = $this->findDefenderLeveledUnit(GameUnitEnum::BUNKER);
        if ($bunker === null) {
            return $power;
        }

        $troops = 0;
        foreach ($this->defenderGameUnits as $defenderGameUnit) {
            if (
                $defenderGameUnit instanceof WorldRegionStackableUnit
                && $this->hasGameUnitCategory($defenderGameUnit, GameUnitCategory::TROOPS)
            ) {
                $troops += $defenderGameUnit->getAmount();
            }
        }

        $sheltered = min($this->getBunkerCapacity($bunker), $troops);
        if ($sheltered <= 0) {
            return $power;
        }

        if (!$this->bunkerShelterLogged) {
            $this->bunkerShelterLogged = true;
            $this->addToBattleLog("Defending {$this->getUnitName($bunker)} shelters {$sheltered} of {$troops} troops");
        }

        return (int) round($power * (1 - self::BUNKER_DAMAGE_REDUCTION * $sheltered / $troops));
    }

    /**
     * Attacking Artillery bombard the Bunker before the battle, ignoring its armor
     */
    private function bombardBunker(): void
    {
        $bunker = $this->findDefenderLeveledUnit(GameUnitEnum::BUNKER);
        if ($bunker === null) {
            return;
        }

        $rawPower = 0;
        foreach ($this->attackerGameUnits as $fleetUnit) {
            if ($fleetUnit->getGameUnit() === GameUnitEnum::ARTILLERY) {
                $rawPower += $this->getUnitAttackPower($fleetUnit);
            }
        }

        $power = (int) ($rawPower * $this->defenderDamageMultiplier);
        if ($power <= 0) {
            return;
        }

        $this->addToBattleLog(
            "Attacking Artillery bombard the defending {$this->getUnitName($bunker)} for {$power} damage"
        );

        $remainingHealth = $bunker->getHealth() - $power;
        if ($remainingHealth <= 0) {
            $this->removeDefenderGameUnit($bunker);
            $this->addToBattleLog("Defending {$this->getUnitName($bunker)} was destroyed");
            return;
        }

        $bunker->setHealth($remainingHealth);
    }

    private function getBunkerCapacity(WorldRegionLeveledUnit $bunker): int
    {
        $maxHealth = $this->getMaxHealth($bunker);
        if ($maxHealth <= 0) {
            return 0;
        }

        return intdiv(self::BUNKER_CAPACITY_PER_LEVEL * $bunker->getLevel() * $bunker->getHealth(), $maxHealth);
    }
}
