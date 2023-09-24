<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\AbstractBattleStats;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use RuntimeException;

abstract class BattlePhase implements IBattlePhase
{
    public const AIR_PHASE = 'air';
    public const SEA_PHASE = 'sea';
    public const GROUND_PHASE = 'ground';

    protected string $name;

    /**
     * @var FleetUnit[]
     */
    protected array $attackerGameUnits;

    /**
     * @var WorldRegionUnit[]
     */
    protected array $defenderGameUnits;

    /**
     * @var array <string>
     */
    protected array $battleLog = [];

    /**
     * BattlePhase constructor.
     *
     * @param string $name
     * @param FleetUnit[] $attackerGameUnits
     * @param WorldRegionUnit[] $defenderGameUnits
     */
    private function __construct(string $name, array $attackerGameUnits, array $defenderGameUnits)
    {
        $this->name = $name;
        $this->attackerGameUnits = $attackerGameUnits;
        $this->defenderGameUnits = $defenderGameUnits;
    }

    /**
     * @param string $battlePhaseName
     * @param FleetUnit[] $attackerGameUnits
     * @param WorldRegionUnit[] $defenderGameUnits
     * @return BattlePhase
     */
    public static function factory(
        string $battlePhaseName,
        array $attackerGameUnits,
        array $defenderGameUnits
    ): BattlePhase {
        $className = "FrankProjects\\UltimateWarfare\\Service\\BattleEngine\\BattlePhase\\" . ucfirst($battlePhaseName);
        if (!class_exists($className) || is_subclass_of($className, __CLASS__) === false) {
            throw new RuntimeException("Unknown BattlePhase {$battlePhaseName}");
        }

        return new $className($battlePhaseName, $attackerGameUnits, $defenderGameUnits);
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
     * @return WorldRegionUnit[]
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

        $attackPower = $this->getAttackPower();
        $this->addToBattleLog("Attacker starts with {$attackPower} attack power");

        if ($attackPower > 0) {
            $this->defenderGameUnits = $this->processBattlePhase($attackPower, $this->defenderGameUnits, 'defending');
        }

        if ($defensePower == 0 && $attackPower == 0) {
            $this->addToBattleLog("No resistance in this battle phase...");
        }
    }

    /**
     * @param FleetUnit[]|WorldRegionUnit[] $gameUnits
     *
     * @return ($action is 'defending' ? WorldRegionUnit[] : FleetUnit[])
     */
    private function processBattlePhase(int $power, array $gameUnits, string $action): array
    {
        foreach ($gameUnits as $index => $gameUnit) {
            $deaths = $this->calculateCasualties($gameUnit->getGameUnit(), $power);

            if ($deaths >= $gameUnit->getAmount()) {
                unset($gameUnits[$index]);
                $this->addToBattleLog("All {$action} {$gameUnit->getGameUnit()->getNameMulti()} died in the fight");
            } elseif ($deaths > 0) {
                $gameUnits[$index]->setAmount($gameUnit->getAmount() - $deaths);
                $this->addToBattleLog(
                    "{$deaths} {$action} {$gameUnit->getGameUnit()->getNameMulti()} died in the fight"
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
            $power += $this->getBattlePhaseBattleStats($fleetUnit->getGameUnit())->getAttack() * $fleetUnit->getAmount();
        }

        return $power;
    }

    public function getDefensePower(): int
    {
        $power = 0;
        foreach ($this->getDefenderGameUnits() as $worldRegionUnit) {
            $power += $this->getBattlePhaseBattleStats($worldRegionUnit->getGameUnit())->getDefence() * $worldRegionUnit->getAmount();
        }

        return $power;
    }
}
