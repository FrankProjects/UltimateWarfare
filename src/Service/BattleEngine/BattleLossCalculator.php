<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;

/**
 * Records the units at the start of a battle, as battle phases change the amounts in place, and
 * calculates what each side lost by the end of it.
 *
 * @phpstan-type LostUnits array{
 *     attacker: array<int, int>,
 *     defender: array<int, int>,
 *     defenderBuildings: array<int, int>
 * }
 */
final class BattleLossCalculator
{
    /**
     * @var array<int, array{FleetUnit, int}>
     */
    private array $attackers = [];

    /**
     * @var array<int, array{WorldRegionStackableUnit|WorldRegionLeveledUnit, int, bool}>
     */
    private array $defenders = [];

    /**
     * @param FleetUnit[] $attackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $defenderGameUnits Including reinforcements
     * @param WorldRegionStackableUnit[] $reinforcementGameUnits
     */
    public function __construct(array $attackerGameUnits, array $defenderGameUnits, array $reinforcementGameUnits = [])
    {
        foreach ($attackerGameUnits as $fleetUnit) {
            $this->attackers[] = [$fleetUnit, $fleetUnit->getAmount()];
        }

        foreach ($defenderGameUnits as $defenderGameUnit) {
            $start = $defenderGameUnit instanceof WorldRegionLeveledUnit
                ? $defenderGameUnit->getLevel()
                : $defenderGameUnit->getAmount();
            $isReinforcement = in_array($defenderGameUnit, $reinforcementGameUnits, true);
            $this->defenders[] = [$defenderGameUnit, $start, $isReinforcement];
        }
    }

    /**
     * Units are keyed by game unit enum value with the amount lost; buildings with the level they had.
     * When the attacker won, the defender loses everything that was in the region, but reinforcements
     * that survived return home.
     *
     * @param FleetUnit[] $survivingAttackerGameUnits
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $survivingDefenderGameUnits
     *
     * @return LostUnits
     */
    public function calculate(
        array $survivingAttackerGameUnits,
        array $survivingDefenderGameUnits,
        bool $attackerWon
    ): array {
        $lostUnits = ['attacker' => [], 'defender' => [], 'defenderBuildings' => []];

        foreach ($this->attackers as [$fleetUnit, $start]) {
            $current = in_array($fleetUnit, $survivingAttackerGameUnits, true) ? $fleetUnit->getAmount() : 0;
            $lostUnits['attacker'] = $this->addLoss(
                $lostUnits['attacker'],
                $fleetUnit->getGameUnit()->value,
                $start - $current
            );
        }

        foreach ($this->defenders as [$defenderGameUnit, $start, $isReinforcement]) {
            $survived = in_array($defenderGameUnit, $survivingDefenderGameUnits, true)
                && ($isReinforcement || !$attackerWon);

            if ($defenderGameUnit instanceof WorldRegionLeveledUnit) {
                if (!$survived) {
                    $lostUnits['defenderBuildings'][$defenderGameUnit->getGameUnit()->value] = $start;
                }
                continue;
            }

            $current = $survived ? $defenderGameUnit->getAmount() : 0;
            $lostUnits['defender'] = $this->addLoss(
                $lostUnits['defender'],
                $defenderGameUnit->getGameUnit()->value,
                $start - $current
            );
        }

        return $lostUnits;
    }

    /**
     * @param array<int, int> $losses
     *
     * @return array<int, int>
     */
    private function addLoss(array $losses, int $gameUnit, int $lost): array
    {
        if ($lost > 0) {
            $losses[$gameUnit] = ($losses[$gameUnit] ?? 0) + $lost;
        }

        return $losses;
    }
}
