<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;

/**
 * The units in a region share one morale, as do the units of a fleet. Fighting lowers it and lower morale weakens
 * units in battle; leveled buildings have no morale. The attacker pays a fixed cost per battle, defenders only lose
 * morale in proportion to their casualties, so a stream of small attacks can't wear a defender down.
 *
 * A region's morale is stored as a checkpoint (morale at the morale timestamp) and recovers continuously from there,
 * measured against the Unix epoch so checkpointing at any moment loses nothing to rounding. Fleets don't recover
 * while they are away: their units rest once they join a region again.
 */
final class MoraleUpdaterService
{
    public const int MAX_MORALE = 100;
    private const int RECOVERY_PER_HOUR = 4;
    private const int SECONDS_PER_HOUR = 3600;

    /**
     * Share of their strength that units keep at 0 morale
     */
    private const float MIN_STRENGTH = 0.5;

    private const int ATTACK_COST = 10;
    private const int ATTACK_DEFEAT_COST = 10;
    private const int ATTACKER_CASUALTY_COST = 40;
    private const int DEFENDER_CASUALTY_COST = 30;

    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(GameUnitRegistry $gameUnitRegistry)
    {
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    /**
     * The morale at $at, without changing the region or fleet
     */
    public function project(WorldRegion|Fleet $holder, int $at): int
    {
        if ($holder instanceof Fleet) {
            return $holder->getMorale();
        }

        $from = $holder->getMoraleTimestamp();
        if ($at <= $from) {
            return $holder->getMorale();
        }

        $gain = intdiv(self::RECOVERY_PER_HOUR * $at, self::SECONDS_PER_HOUR)
            - intdiv(self::RECOVERY_PER_HOUR * $from, self::SECONDS_PER_HOUR);

        return min(self::MAX_MORALE, $holder->getMorale() + $gain);
    }

    /**
     * Factor applied to the attack and defense power of units fighting at $morale
     */
    public function getStrengthMultiplier(int $morale): float
    {
        return self::MIN_STRENGTH + (1 - self::MIN_STRENGTH) * $morale / self::MAX_MORALE;
    }

    public function setRegionMorale(WorldRegion $worldRegion, int $morale, int $at): void
    {
        $worldRegion->setMorale(max(0, min(self::MAX_MORALE, $morale)));
        $worldRegion->setMoraleTimestamp($at);
    }

    /**
     * The people crewing the units; morale is averaged and casualties are weighed by crew
     *
     * @param iterable<FleetUnit|WorldRegionStackableUnit> $gameUnits
     */
    public function getCrew(iterable $gameUnits): int
    {
        $crew = 0;
        foreach ($gameUnits as $gameUnit) {
            $crew += $this->getUnitCrew($gameUnit->getGameUnit(), $gameUnit->getAmount());
        }

        return $crew;
    }

    public function getUnitCrew(GameUnitEnum $gameUnitEnum, int $amount): int
    {
        // Every unit counts, even one without crew
        return max(1, $this->gameUnitRegistry->find($gameUnitEnum)->getPopulation()) * $amount;
    }

    /**
     * Units with $morale and $crew join the region: its morale becomes the crew-weighted average. Call this
     * before the units are added to the region.
     */
    public function mergeIntoRegion(WorldRegion $worldRegion, int $morale, int $crew, int $at): void
    {
        $regionCrew = $this->getCrew($worldRegion->getWorldRegionStackableUnits());
        if ($regionCrew + $crew === 0) {
            return;
        }

        $regionMorale = $this->project($worldRegion, $at);
        $average = (int) round(($regionMorale * $regionCrew + $morale * $crew) / ($regionCrew + $crew));
        $this->setRegionMorale($worldRegion, $average, $at);
    }

    public function applyAttackerLoss(Fleet $fleet, int $startCrew, int $lostCrew, bool $won): void
    {
        $loss = self::ATTACK_COST
            + $this->getCasualtyCost(self::ATTACKER_CASUALTY_COST, $startCrew, $lostCrew)
            + ($won ? 0 : self::ATTACK_DEFEAT_COST);

        $fleet->setMorale(max(0, $fleet->getMorale() - $loss));
    }

    public function applyDefenderLoss(WorldRegion $worldRegion, int $startCrew, int $lostCrew, int $at): void
    {
        $loss = $this->getCasualtyCost(self::DEFENDER_CASUALTY_COST, $startCrew, $lostCrew);
        if ($loss === 0) {
            return;
        }

        $this->setRegionMorale($worldRegion, $this->project($worldRegion, $at) - $loss, $at);
    }

    private function getCasualtyCost(int $cost, int $startCrew, int $lostCrew): int
    {
        if ($startCrew <= 0 || $lostCrew <= 0) {
            return 0;
        }

        return (int) round($cost * min($lostCrew, $startCrew) / $startCrew);
    }
}
