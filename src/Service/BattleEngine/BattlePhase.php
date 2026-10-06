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
     * XXX TODO: Add attack/defense speed from BattleStats
     */
    public function startBattlePhase(): void
    {
        $this->addToBattleLog("Starting {$this->getName()} Battle Phase");

        $defensePower = $this->getDefensePower();
        $this->addToBattleLog("Defender starts with {$defensePower} defense power");

        if ($defensePower > 0) {
            $this->attackerGameUnits = $this->processBattlePhase($defensePower, $this->attackerGameUnits, 'attacking');
        }

        $rawAttackPower = $this->getAttackPower();
        $attackPower = (int) ($rawAttackPower * $this->defenderDamageMultiplier);
        if ($this->defenderDamageMultiplier < 1.0 && $rawAttackPower > 0) {
            $this->addToBattleLog(
                "Defender's defensive network reduces incoming damage from {$rawAttackPower} to {$attackPower}"
            );
        }
        $this->addToBattleLog("Attacker starts with {$attackPower} attack power");

        if ($attackPower > 0) {
            $this->defenderGameUnits = $this->processBattlePhase($attackPower, $this->defenderGameUnits, 'defending');
        }

        if ($defensePower === 0 && $attackPower === 0) {
            $this->addToBattleLog("No resistance in this battle phase...");
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
                $damage = $armor > 0 ? intval($power / $armor) : $power;
                $remainingHealth = $gameUnit->getHealth() - $damage;

                if ($remainingHealth <= 0) {
                    unset($gameUnits[$index]);
                    $this->addToBattleLog(
                        "{$action} {$resolvedUnit->getName()} (level {$gameUnit->getLevel()}) was destroyed"
                    );
                } else {
                    $gameUnit->setHealth($remainingHealth);
                    $this->addToBattleLog(
                        "{$action} {$resolvedUnit->getName()} (level {$gameUnit->getLevel()}) "
                        . "took {$damage} damage, {$remainingHealth} health remaining"
                    );
                }

                continue;
            }

            $deaths = $this->calculateCasualties($resolvedUnit, $power);

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

    private function calculateCasualties(GameUnit $gameUnit, int $power): int
    {
        $health = $gameUnit->getBattleStats()->getHealth();

        if ($health <= 0) {
            return 0;
        }

        $armor = $gameUnit->getBattleStats()->getArmor();
        if ($armor > 0) {
            $health = $health * $armor;
        }

        return intval($power / $health);
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
            $gameUnit = $this->gameUnitRegistry->find($fleetUnit->getGameUnit());
            $power += $this->getBattlePhaseBattleStats($gameUnit)->getAttack()
                * $fleetUnit->getAmount();
        }

        return $power;
    }

    public function getDefensePower(): int
    {
        $power = 0;
        foreach ($this->getDefenderGameUnits() as $worldRegionStackableUnit) {
            $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            // Leveled buildings contribute defence per level; stackable units per amount.
            $multiplier = $worldRegionStackableUnit instanceof WorldRegionLeveledUnit
                ? $worldRegionStackableUnit->getLevel()
                : $worldRegionStackableUnit->getAmount();
            $power += $this->getBattlePhaseBattleStats($gameUnit)->getDefence() * $multiplier;
        }

        return $power;
    }
}
