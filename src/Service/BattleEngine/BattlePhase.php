<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\BattleStats\AbstractBattleStats;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use RuntimeException;

abstract class BattlePhase implements IBattlePhase
{
    public const string AIR_PHASE = 'air';
    public const string SEA_PHASE = 'sea';
    public const string GROUND_PHASE = 'ground';

    /**
     * Share of their max health that mines spend per detonation
     */
    private const float MINE_DETONATION_COST = 0.5;

    protected string $name;

    /**
     * @var FleetUnit[]
     */
    protected array $attackerGameUnits;

    /**
     * @var array<WorldRegionStackableUnit|WorldRegionLeveledUnit>
     */
    protected array $defenderGameUnits;

    /**
     * @var array <string>
     */
    protected array $battleLog = [];

    protected GameUnitRegistry $gameUnitRegistry;

    protected float $defenderDamageMultiplier;

    /**
     * Power per unit group that was not enough for a whole death or health point, keyed by object id
     *
     * @var array<int, int>
     */
    private array $carriedPower = [];

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    private function __construct(
        string $name,
        array $attackerGameUnits,
        array $defenderGameUnits,
        GameUnitRegistry $gameUnitRegistry,
        float $defenderDamageMultiplier = 1.0
    ) {
        $this->name = $name;
        $this->attackerGameUnits = $attackerGameUnits;
        $this->defenderGameUnits = $defenderGameUnits;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->defenderDamageMultiplier = $defenderDamageMultiplier;
    }

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits
     */
    public static function factory(
        string $battlePhaseName,
        array $attackerGameUnits,
        array $defenderGameUnits,
        GameUnitRegistry $gameUnitRegistry,
        float $defenderDamageMultiplier = 1.0
    ): BattlePhase {
        $className = "FrankProjects\\UltimateWarfare\\Service\\BattleEngine\\BattlePhase\\" . ucfirst($battlePhaseName);
        if (!class_exists($className) || is_subclass_of($className, __CLASS__) === false) {
            throw new RuntimeException("Unknown BattlePhase {$battlePhaseName}");
        }

        return new $className(
            $battlePhaseName,
            $attackerGameUnits,
            $defenderGameUnits,
            $gameUnitRegistry,
            $defenderDamageMultiplier
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return FleetUnit[]
     */
    public function getAttackerGameUnits(): array
    {
        return $this->attackerGameUnits;
    }

    /**
     * @return array<WorldRegionStackableUnit|WorldRegionLeveledUnit>
     */
    public function getDefenderGameUnits(): array
    {
        return $this->defenderGameUnits;
    }

    /**
     * @return array<string>
     */
    public function getBattleLog(): array
    {
        return $this->battleLog;
    }

    protected function addToBattleLog(string $log): void
    {
        $this->battleLog[] = $log;
    }

    /**
     * Unit groups fire in order of their speed in this phase, fastest first. Groups sharing a speed
     * fire simultaneously, and a group that is destroyed before its turn never fires.
     */
    public function startBattlePhase(): void
    {
        $this->addToBattleLog("Starting {$this->getName()} Battle Phase");

        $this->beforeSpeedTiers();

        $speeds = $this->getSpeeds();
        if ($speeds === [] && count($this->battleLog) === 1) {
            $this->addToBattleLog("No resistance in this battle phase...");
            return;
        }

        foreach ($speeds as $speed) {
            $this->processSpeedTier($speed);
        }
    }

    /**
     * Phase specific actions that happen before the first unit group fires
     */
    protected function beforeSpeedTiers(): void
    {
    }

    /**
     * Whether a defending unit group fires in the speed tiers; groups that don't still count as defense power
     */
    protected function firesInSpeedTiers(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): bool
    {
        return true;
    }

    /**
     * The power of a volley that actually reaches a unit group
     */
    protected function getIncomingPower(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit,
        int $power,
        string $action
    ): int {
        return $power;
    }

    /**
     * @return array<int, int> Distinct speeds of all unit groups with power in this phase, fastest first
     */
    private function getSpeeds(): array
    {
        $speeds = [];
        foreach ($this->attackerGameUnits as $fleetUnit) {
            if ($this->getUnitAttackPower($fleetUnit) > 0) {
                $speeds[] = $this->getUnitBattleStats($fleetUnit)->getAttackSpeed();
            }
        }

        foreach ($this->defenderGameUnits as $defenderGameUnit) {
            if ($this->firesInSpeedTiers($defenderGameUnit) && $this->getUnitDefensePower($defenderGameUnit) > 0) {
                $speeds[] = $this->getUnitBattleStats($defenderGameUnit)->getDefenceSpeed();
            }
        }

        $speeds = array_values(array_unique($speeds));
        rsort($speeds);

        return $speeds;
    }

    private function processSpeedTier(int $speed): void
    {
        $defensePower = 0;
        $defenderNames = [];
        foreach ($this->defenderGameUnits as $defenderGameUnit) {
            $power = $this->getUnitDefensePower($defenderGameUnit);
            if (
                $power > 0
                && $this->firesInSpeedTiers($defenderGameUnit)
                && $this->getUnitBattleStats($defenderGameUnit)->getDefenceSpeed() === $speed
            ) {
                $defensePower += $power;
                $defenderNames[] = $this->getUnitName($defenderGameUnit);
            }
        }

        $rawAttackPower = 0;
        $attackerNames = [];
        foreach ($this->attackerGameUnits as $fleetUnit) {
            $power = $this->getUnitAttackPower($fleetUnit);
            if ($power > 0 && $this->getUnitBattleStats($fleetUnit)->getAttackSpeed() === $speed) {
                $rawAttackPower += $power;
                $attackerNames[] = $this->getUnitName($fleetUnit);
            }
        }

        // Every group in this tier already died in a faster tier
        if ($defensePower === 0 && $rawAttackPower === 0) {
            return;
        }

        $attackPower = (int) ($rawAttackPower * $this->defenderDamageMultiplier);

        $firing = [];
        if ($defensePower > 0) {
            $firing[] = "defending " . implode(', ', $defenderNames) . " fire with {$defensePower} defense power";
        }
        if ($rawAttackPower > 0) {
            $firing[] = "attacking " . implode(', ', $attackerNames) . " fire with {$rawAttackPower} attack power";
        }
        $this->addToBattleLog(ucfirst(implode(', ', $firing)));

        if ($this->defenderDamageMultiplier < 1.0 && $rawAttackPower > 0) {
            $this->addToBattleLog(
                "Defender's defensive network reduces incoming damage from {$rawAttackPower} to {$attackPower}"
            );
        }

        // Both powers are determined before any casualties, so both sides fire simultaneously
        if ($defensePower > 0) {
            $this->attackerGameUnits = $this->processBattlePhase($defensePower, $this->attackerGameUnits, 'attacking');
        }

        if ($attackPower > 0) {
            $this->defenderGameUnits = $this->processBattlePhase($attackPower, $this->defenderGameUnits, 'defending');
        }
    }

    /**
     * Armor-piercing damage kills a unit per point of health, ignoring its armor.
     *
     * @param array<FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit> $gameUnits
     *
     * @return ($action is 'defending' ? array<WorldRegionStackableUnit|WorldRegionLeveledUnit> : FleetUnit[])
     */
    protected function processBattlePhase(
        int $volleyPower,
        array $gameUnits,
        string $action,
        bool $armorPiercing = false
    ): array {
        foreach ($gameUnits as $index => $gameUnit) {
            if (!$this->takesPartInPhase($gameUnit)) {
                continue;
            }

            $resolvedUnit = $this->gameUnitRegistry->find($gameUnit->getGameUnit());
            $power = $this->getIncomingPower($gameUnit, $volleyPower, $action);

            // Leveled buildings (Defense / Special) absorb damage on their health pool.
            // Their level is never reduced by combat, only their health.
            if ($gameUnit instanceof WorldRegionLeveledUnit) {
                $armor = $resolvedUnit->getBattleStats()->getArmor();
                $damage = $this->applyCarriedDamage($gameUnit, $power, $armor > 0 ? $armor : 1);
                $remainingHealth = $gameUnit->getHealth() - $damage;

                if ($remainingHealth <= 0) {
                    unset($gameUnits[$index]);
                    $this->addToBattleLog(
                        "{$action} {$resolvedUnit->getName()} (level {$gameUnit->getLevel()}) was destroyed"
                    );
                } elseif ($damage > 0) {
                    $gameUnit->setHealth($remainingHealth);
                    $this->addToBattleLog(
                        "{$action} {$resolvedUnit->getName()} (level {$gameUnit->getLevel()}) "
                        . "took {$damage} damage, {$remainingHealth} health remaining"
                    );
                }

                continue;
            }

            $healthPerUnit = $armorPiercing
                ? $resolvedUnit->getBattleStats()->getHealth()
                : $this->getHealthPerUnit($resolvedUnit);
            if ($healthPerUnit <= 0) {
                continue;
            }

            $deaths = $this->applyCarriedDamage($gameUnit, $power, $healthPerUnit);

            if ($deaths >= $gameUnit->getAmount()) {
                unset($gameUnits[$index]);
                $this->addToBattleLog("All {$action} {$resolvedUnit->getNameMulti()} died in the fight");
            } elseif ($deaths > 0) {
                $gameUnit->setAmount($gameUnit->getAmount() - $deaths);
                $this->addToBattleLog(
                    "{$deaths} {$action} {$resolvedUnit->getNameMulti()} died in the fight"
                );
            }
        }

        return $gameUnits;
    }

    /**
     * Converts power into whole units of damage (deaths or health points). Power that is not enough
     * for a whole unit is carried over to the next volley, so splitting a battle phase into speed
     * tiers loses no damage to rounding.
     */
    private function applyCarriedDamage(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit,
        int $power,
        int $powerPerUnit
    ): int {
        $key = spl_object_id($gameUnit);
        $totalPower = $power + ($this->carriedPower[$key] ?? 0);
        $this->carriedPower[$key] = $totalPower % $powerPerUnit;

        return intdiv($totalPower, $powerPerUnit);
    }

    /**
     * Only unit groups fighting in this phase can be hit, so e.g. fighters in the air phase do not
     * strike ground troops. Every leveled building can be hit in the ground phase.
     */
    private function takesPartInPhase(FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit): bool
    {
        if ($this->name === self::GROUND_PHASE && $gameUnit instanceof WorldRegionLeveledUnit) {
            return true;
        }

        $battleStats = $this->getUnitBattleStats($gameUnit);

        return $battleStats->getAttack() > 0 || $battleStats->getDefence() > 0;
    }

    private function getHealthPerUnit(GameUnit $gameUnit): int
    {
        $health = $gameUnit->getBattleStats()->getHealth();
        $armor = $gameUnit->getBattleStats()->getArmor();
        if ($health > 0 && $armor > 0) {
            $health = $health * $armor;
        }

        return $health;
    }

    protected function getGameUnit(FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit): GameUnit
    {
        return $this->gameUnitRegistry->find($gameUnit->getGameUnit());
    }

    protected function findDefenderLeveledUnit(GameUnitEnum $gameUnitEnum): ?WorldRegionLeveledUnit
    {
        foreach ($this->defenderGameUnits as $defenderGameUnit) {
            if (
                $defenderGameUnit instanceof WorldRegionLeveledUnit
                && $defenderGameUnit->getGameUnit() === $gameUnitEnum
            ) {
                return $defenderGameUnit;
            }
        }

        return null;
    }

    protected function removeDefenderGameUnit(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): void
    {
        $index = array_search($defenderGameUnit, $this->defenderGameUnits, true);
        if ($index !== false) {
            unset($this->defenderGameUnits[$index]);
        }
    }

    protected function getMaxHealth(WorldRegionLeveledUnit $leveledUnit): int
    {
        return $this->getGameUnit($leveledUnit)->getBattleStats()->getHealth() * $leveledUnit->getLevel();
    }

    /**
     * Attacking sweepers clear mines before they can detonate
     */
    protected function sweepMines(GameUnitEnum $mineEnum, GameUnitEnum $sweeperEnum, int $minesClearedPerSweeper): void
    {
        $mines = $this->findDefenderLeveledUnit($mineEnum);
        if ($mines === null) {
            return;
        }

        $sweepers = 0;
        foreach ($this->attackerGameUnits as $fleetUnit) {
            if ($fleetUnit->getGameUnit() === $sweeperEnum) {
                $sweepers += $fleetUnit->getAmount();
            }
        }

        if ($sweepers === 0) {
            return;
        }

        $cleared = min($sweepers * $minesClearedPerSweeper, $mines->getHealth());
        $sweeperName = $this->gameUnitRegistry->find($sweeperEnum)->getNameMulti();
        $this->addToBattleLog("Attacking {$sweeperName} clear {$cleared} mines");

        $remainingHealth = $mines->getHealth() - $cleared;
        if ($remainingHealth <= 0) {
            $this->removeDefenderGameUnit($mines);
            $this->addToBattleLog("Defending {$this->getUnitName($mines)} was cleared");
            return;
        }

        $mines->setHealth($remainingHealth);
    }

    /**
     * Mines strike the attacking units of the target category first with armor-piercing damage, scaled by
     * the remaining mines. Detonated mines are spent, which costs the mines part of their health.
     */
    protected function detonateMines(GameUnitEnum $mineEnum, GameUnitCategory $targetCategory): void
    {
        $mines = $this->findDefenderLeveledUnit($mineEnum);
        if ($mines === null) {
            return;
        }

        $maxHealth = $this->getMaxHealth($mines);
        $targets = array_filter(
            $this->attackerGameUnits,
            fn (FleetUnit $fleetUnit): bool => $this->hasGameUnitCategory($fleetUnit, $targetCategory)
        );
        if ($maxHealth <= 0 || $targets === []) {
            return;
        }

        $power = intdiv($this->getUnitDefensePower($mines) * $mines->getHealth(), $maxHealth);
        if ($power <= 0) {
            return;
        }

        $this->addToBattleLog(
            "Defending {$this->getUnitName($mines)} detonates with {$power} armor-piercing power"
        );

        $survivors = $this->processBattlePhase($power, $targets, 'attacking', true);
        foreach (array_keys(array_diff_key($targets, $survivors)) as $index) {
            unset($this->attackerGameUnits[$index]);
        }

        $remainingHealth = $mines->getHealth() - (int) ceil($maxHealth * self::MINE_DETONATION_COST);
        if ($remainingHealth <= 0) {
            $this->removeDefenderGameUnit($mines);
            $this->addToBattleLog("Defending {$this->getUnitName($mines)} has no mines left");
            return;
        }

        $mines->setHealth($remainingHealth);
        $this->addToBattleLog("Defending {$this->getUnitName($mines)} has {$remainingHealth} mine health left");
    }

    protected function hasGameUnitCategory(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit,
        GameUnitCategory $gameUnitCategory
    ): bool {
        return $this->getGameUnit($gameUnit)->getGameUnitCategory() === $gameUnitCategory;
    }

    private function getUnitBattleStats(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit
    ): AbstractBattleStats {
        return $this->getBattlePhaseBattleStats($this->getGameUnit($gameUnit));
    }

    protected function getUnitName(FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit): string
    {
        $resolvedUnit = $this->getGameUnit($gameUnit);

        return $gameUnit instanceof WorldRegionLeveledUnit
            ? "{$resolvedUnit->getName()} (level {$gameUnit->getLevel()})"
            : $resolvedUnit->getNameMulti();
    }

    protected function getUnitAttackPower(FleetUnit $fleetUnit): int
    {
        return $this->getUnitBattleStats($fleetUnit)->getAttack() * $fleetUnit->getAmount();
    }

    protected function getUnitDefensePower(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): int
    {
        // Leveled buildings contribute defence per level; stackable units per amount.
        $multiplier = $defenderGameUnit instanceof WorldRegionLeveledUnit
            ? $defenderGameUnit->getLevel()
            : $defenderGameUnit->getAmount();

        return $this->getUnitBattleStats($defenderGameUnit)->getDefence() * $multiplier;
    }

    private function getBattlePhaseBattleStats(GameUnit $gameUnit): AbstractBattleStats
    {
        if ($this->name === BattlePhase::AIR_PHASE) {
            return $gameUnit->getBattleStats()->getAirBattleStats();
        } elseif ($this->name === BattlePhase::SEA_PHASE) {
            return $gameUnit->getBattleStats()->getSeaBattleStats();
        } elseif ($this->name === BattlePhase::GROUND_PHASE) {
            return $gameUnit->getBattleStats()->getGroundBattleStats();
        }

        throw new RuntimeException("Invalid BattleStats for {$this->name}");
    }

    public function getAttackPower(): int
    {
        $power = 0;
        foreach ($this->getAttackerGameUnits() as $fleetUnit) {
            $power += $this->getUnitAttackPower($fleetUnit);
        }

        return $power;
    }

    public function getDefensePower(): int
    {
        $power = 0;
        foreach ($this->getDefenderGameUnits() as $defenderGameUnit) {
            $power += $this->getUnitDefensePower($defenderGameUnit);
        }

        return $power;
    }
}
