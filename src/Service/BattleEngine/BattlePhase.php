<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use RuntimeException;

abstract class BattlePhase implements IBattlePhase
{
    const AIR_PHASE = 'air';
    const SEA_PHASE = 'sea';
    const GROUND_PHASE = 'ground';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var FleetUnit[]
     */
    protected $attackerGameUnits;

    /**
     * @var WorldRegionUnit[]
     */
    protected $defenderGameUnits;

    /**
     * @var array
     */
    protected $battleLog = [];

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
    public static function factory(string $battlePhaseName, array $attackerGameUnits, array $defenderGameUnits): BattlePhase
    {
        $className = "FrankProjects\\UltimateWarfare\\Service\\BattleEngine\\BattlePhase\\" . ucfirst($battlePhaseName);
        if (!class_exists($className)) {
            throw new RunTimeException("Unknown BattlePhase {$battlePhaseName}");
        }

        return new $className($battlePhaseName, $attackerGameUnits, $defenderGameUnits);
    }

    /**
     * @return string
     */
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
     * @return array
     */
    public function getBattleLog(): array
    {
        return $this->battleLog;
    }

    /**
     * @param string $log
     */
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
        if ($defensePower > 0) {
            $this->defensePhase($defensePower);
        }

        $attackPower = $this->getAttackPower();
        if ($attackPower > 0) {
            $this->attackPhase($attackPower);
        }

        if ($defensePower == 0 && $attackPower == 0) {
            $this->addToBattleLog("No resistance in this battle phase...");
        }
    }

    /**
     * @param int $defensePower
     */
    private function defensePhase(int $defensePower): void
    {
        $this->addToBattleLog("Defender starts with {$defensePower} defense power");

        foreach ($this->attackerGameUnits as $index => $gameUnit) {
            $deaths = $this->calculateCasualties($gameUnit->getGameUnit(), $defensePower);


            if ($deaths >= $gameUnit->getAmount()) {
                unset($this->attackerGameUnits[$index]);
                $this->addToBattleLog("All attacking {$gameUnit->getGameUnit()->getNameMulti()} died in the fight");

            } elseif ($deaths > 0) {
                $this->attackerGameUnits[$index]->setAmount($gameUnit->getAmount() - $deaths);
                $this->addToBattleLog("{$deaths} attacking {$gameUnit->getGameUnit()->getNameMulti()} died in the fight");
            }
        }
    }

    /**
     * @param int $attackPower
     */
    private function attackPhase(int $attackPower): void
    {
        $this->addToBattleLog("Attacker starts with {$attackPower} attack power");

        foreach ($this->defenderGameUnits as $index => $gameUnit) {
            $deaths = $this->calculateCasualties($gameUnit->getGameUnit(), $attackPower);

            if ($deaths >= $gameUnit->getAmount()) {
                unset($this->defenderGameUnits[$index]);
                $this->addToBattleLog("All defending {$gameUnit->getGameUnit()->getNameMulti()} died in the fight");
            } elseif ($deaths > 0) {
                $this->defenderGameUnits[$index]->setAmount($gameUnit->getAmount() - $deaths);
                $this->addToBattleLog("{$deaths} defending {$gameUnit->getGameUnit()->getNameMulti()} died in the fight");
            }
        }
    }

    /**
     * @param GameUnit $gameUnit
     * @param int $power
     * @return int
     */
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
}
