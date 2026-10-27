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
 * Before the speed tiers, the Anti Aircraft Gun fires flak at the attacking aircraft. During the speed tiers
 * Fighters escort the Bombers of their side. A Radar Station strengthens all defending air power.
 */
final class Air extends BattlePhase
{
    private const float FIGHTER_FLAK_DAMAGE = 0.5;
    private const float ESCORT_DAMAGE_REDUCTION = 0.75;
    private const float RADAR_BONUS_PER_LEVEL = 0.1;

    private bool $resolvingFlak = false;

    /**
     * Share of the bombers escorted by fighters, on the side taking the current volley
     */
    private float $escortShare = 0.0;

    /**
     * @var array<string, bool>
     */
    private array $escortLogged = [];

    protected function beforeSpeedTiers(): void
    {
        $radar = $this->findDefenderLeveledUnit(GameUnitEnum::RADAR_STATION);
        $radarBonus = $this->getRadarBonus();
        if ($radar !== null && $radarBonus > 0 && $this->getDefensePower() > 0) {
            $percentage = (int) round($radarBonus * 100);
            $this->addToBattleLog(
                "Defending {$this->getUnitName($radar)} guides the air defence: +{$percentage}% defense power"
            );
        }

        $this->fireFlak();
    }

    /**
     * Anti Aircraft Guns only strike once, when they fire flak before the speed tiers
     */
    protected function firesInSpeedTiers(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): bool
    {
        return $defenderGameUnit->getGameUnit() !== GameUnitEnum::ANTI_AIRCRAFT_GUN;
    }

    /**
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
        // Escorts are determined before the volley, so escorting fighters shot down by it still protect the bombers
        $this->escortShare = $this->resolvingFlak ? 0.0 : $this->getEscortShare($action);

        return parent::processBattlePhase($volleyPower, $gameUnits, $action, $armorPiercing);
    }

    /**
     * Escorted bombers take reduced damage from aircraft fire, but not from flak
     */
    protected function getIncomingPower(
        FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit,
        int $power,
        string $action
    ): int {
        if ($this->escortShare <= 0 || !$this->isBomber($gameUnit)) {
            return $power;
        }

        return (int) round($power * (1 - self::ESCORT_DAMAGE_REDUCTION * $this->escortShare));
    }

    /**
     * The Radar Station strengthens defending air power, scaled by its remaining health
     */
    protected function getUnitDefensePower(WorldRegionStackableUnit|WorldRegionLeveledUnit $defenderGameUnit): int
    {
        $power = parent::getUnitDefensePower($defenderGameUnit);
        $radarBonus = $this->getRadarBonus();

        return $radarBonus > 0 ? (int) round($power * (1 + $radarBonus)) : $power;
    }

    /**
     * Flak ignores the armor of bombers, while agile fighters take only part of it
     */
    private function fireFlak(): void
    {
        $antiAircraftGun = $this->findDefenderLeveledUnit(GameUnitEnum::ANTI_AIRCRAFT_GUN);
        if ($antiAircraftGun === null) {
            return;
        }

        $flakPower = $this->getUnitDefensePower($antiAircraftGun);
        $aircraft = array_filter(
            $this->attackerGameUnits,
            fn (FleetUnit $fleetUnit): bool => $this->hasGameUnitCategory($fleetUnit, GameUnitCategory::AIR_UNITS)
        );
        if ($flakPower <= 0 || $aircraft === []) {
            return;
        }

        $this->addToBattleLog("Defending {$this->getUnitName($antiAircraftGun)} fires flak with {$flakPower} power");

        $this->resolvingFlak = true;
        foreach ($aircraft as $index => $fleetUnit) {
            $isBomber = $this->isBomber($fleetUnit);
            $power = $isBomber ? $flakPower : (int) ($flakPower * self::FIGHTER_FLAK_DAMAGE);

            if ($this->processBattlePhase($power, [$index => $fleetUnit], 'attacking', $isBomber) === []) {
                unset($this->attackerGameUnits[$index]);
            }
        }
        $this->resolvingFlak = false;
    }

    private function getEscortShare(string $action): float
    {
        $gameUnits = $action === 'defending' ? $this->defenderGameUnits : $this->attackerGameUnits;

        $fighters = 0;
        $bombers = 0;
        foreach ($gameUnits as $gameUnit) {
            if ($gameUnit instanceof WorldRegionLeveledUnit) {
                continue;
            }

            if ($gameUnit->getGameUnit() === GameUnitEnum::FIGHTER) {
                $fighters += $gameUnit->getAmount();
            } elseif ($this->isBomber($gameUnit)) {
                $bombers += $gameUnit->getAmount();
            }
        }

        $escorted = min($fighters, $bombers);
        if ($escorted <= 0) {
            return 0.0;
        }

        if (!isset($this->escortLogged[$action])) {
            $this->escortLogged[$action] = true;
            $this->addToBattleLog(ucfirst($action) . " Fighters escort {$escorted} of {$bombers} bombers");
        }

        return $escorted / $bombers;
    }

    private function getRadarBonus(): float
    {
        $radar = $this->findDefenderLeveledUnit(GameUnitEnum::RADAR_STATION);
        if ($radar === null) {
            return 0.0;
        }

        $maxHealth = $this->getMaxHealth($radar);
        if ($maxHealth <= 0) {
            return 0.0;
        }

        return self::RADAR_BONUS_PER_LEVEL * $radar->getLevel() * $radar->getHealth() / $maxHealth;
    }

    private function isBomber(FleetUnit|WorldRegionStackableUnit|WorldRegionLeveledUnit $gameUnit): bool
    {
        return in_array($gameUnit->getGameUnit(), [GameUnitEnum::BOMBER, GameUnitEnum::STRATEGIC_BOMBER], true);
    }
}
