<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

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

        $speeds = $this->getSpeeds();
        if ($speeds === []) {
            $this->addToBattleLog("No resistance in this battle phase...");
            return;
        }

        foreach ($speeds as $speed) {
            $this->processSpeedTier($speed);
        }
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
            if ($this->getUnitDefensePower($defenderGameUnit) > 0) {
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
            if ($power > 0 && $this->getUnitBattleStats($defenderGameUnit)->getDefenceSpeed() === $speed) {
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
     * @param array<FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit> $gameUnits
     *
     * @return ($action is 'defending' ? array<WorldRegionStackableUnit|WorldRegionLeveledUnit> : FleetUnit[])
     */
    private function processBattlePhase(int $power, array $gameUnits, string $action): array
    {
        foreach ($gameUnits as $index => $gameUnit) {
            $resolvedUnit = $this->gameUnitRegistry->find($gameUnit->getGameUnit());

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

            $healthPerUnit = $this->getHealthPerUnit($resolvedUnit);
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

    private function getHealthPerUnit(GameUnit $gameUnit): int
    {
        $health = $gameUnit->getBattleStats()->getHealth();
        $armor = $gameUnit->getBattleStats()->getArmor();
        if ($health > 0 && $armor > 0) {
            $health = $health * $armor;
        }

        return $health;
    }

    private function getUnitBattleStats(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit
    ): AbstractBattleStats {
        return $this->getBattlePhaseBattleStats($this->gameUnitRegistry->find($gameUnit->getGameUnit()));
    }

    private function getUnitName(FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit): string
    {
        $resolvedUnit = $this->gameUnitRegistry->find($gameUnit->getGameUnit());

        return $gameUnit instanceof WorldRegionLeveledUnit
            ? "{$resolvedUnit->getName()} (level {$gameUnit->getLevel()})"
            : $resolvedUnit->getNameMulti();
    }

    private function getUnitAttackPower(FleetUnit $fleetUnit): int
    {
        return $this->getUnitBattleStats($fleetUnit)->getAttack() * $fleetUnit->getAmount();
    }

    private function getUnitDefensePower(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): int
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
