<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;

final class NavalBombardment extends OperationProcessor
{
    private const float MILITARY_POWER_RATIO = 0.75;
    private const float BUILDING_POWER_RATIO = 0.25;
    private const float DIMINISHING_RETURNS_FACTOR = 100.0;
    private const float MAX_DESTRUCTION_RATIO = 0.5;

    public function getFormula(): float
    {
        return 1.0;
    }

    public function processPreOperation(): void
    {
        // Cruisers are not consumed — units remain in the region
    }

    public function processSuccess(): void
    {
        $seaAttackStat = $this->getCruiserGroundAttack();
        $totalPower = $this->amount * $seaAttackStat;
        $diminishing = self::DIMINISHING_RETURNS_FACTOR;
        $effectivePower = $totalPower * ($diminishing / ($diminishing + $totalPower));

        $effectivePowerRounded = intval($effectivePower);
        $this->addToOperationLog(
            "Naval bombardment unleashes {$totalPower} total firepower"
            . " ({$effectivePowerRounded} effective)."
        );

        $militaryPower = $effectivePower * self::MILITARY_POWER_RATIO;
        $buildingPower = $effectivePower * self::BUILDING_POWER_RATIO;

        $militaryUnits = [];
        $buildingUnits = [];

        foreach ($this->region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            if ($gameUnit->getGameUnitCategory()->isSendable()) {
                $militaryUnits[] = $worldRegionStackableUnit;
            } else {
                $buildingUnits[] = $worldRegionStackableUnit;
            }
        }

        $totalDestroyed = 0;
        $totalDestroyed += $this->applyDamageToGroup($militaryUnits, $militaryPower);
        $totalDestroyed += $this->applyDamageToGroup($buildingUnits, $buildingPower);

        if ($totalDestroyed === 0) {
            $this->addToOperationLog("The bombardment caused no significant damage.");
        } else {
            $this->addToOperationLog("Total units destroyed: {$totalDestroyed}");
        }

        $attackerName = $this->getPlayerRegionPlayer()->getName();
        $regionX = $this->region->getX();
        $regionY = $this->region->getY();

        $reportText = "{$attackerName} launched a naval bombardment"
            . " against region {$regionX}, {$regionY} and destroyed {$totalDestroyed} units.";
        $this->reportCreator->createReport($this->getTargetRegionPlayer(), time(), $reportText);
    }

    /**
     * @param WorldRegionStackableUnit[] $units
     */
    private function applyDamageToGroup(array $units, float $power): int
    {
        if ($units === [] || $power <= 0) {
            return 0;
        }

        $totalDestroyed = 0;

        foreach ($units as $worldRegionStackableUnit) {
            $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            $amount = $worldRegionStackableUnit->getAmount();

            $battleStats = $gameUnit->getBattleStats();
            $effectiveHealth = $this->calculateEffectiveHealth(
                $battleStats->getHealth(),
                $battleStats->getArmor()
            );

            if ($effectiveHealth <= 0) {
                continue;
            }

            $casualties = intval($power / $effectiveHealth);
            $maxCasualties = intval($amount * self::MAX_DESTRUCTION_RATIO);
            $casualties = min($casualties, $maxCasualties);

            if ($casualties <= 0) {
                continue;
            }

            if ($casualties >= $amount) {
                $this->worldRegionStackableUnitRepository->remove($worldRegionStackableUnit);
                $this->addToOperationLog("All {$gameUnit->getNameMulti()} destroyed ({$amount})!");
                $totalDestroyed += $amount;
            } else {
                $worldRegionStackableUnit->setAmount($amount - $casualties);
                $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
                $this->addToOperationLog("{$casualties} {$gameUnit->getNameMulti()} destroyed!");
                $totalDestroyed += $casualties;
            }
        }

        return $totalDestroyed;
    }

    private function calculateEffectiveHealth(int $health, int $armor): int
    {
        if ($health <= 0) {
            return 0;
        }

        if ($armor > 0) {
            return $health * $armor;
        }

        return $health;
    }

    private function getCruiserGroundAttack(): int
    {
        $gameUnitEnum = $this->operation->getGameUnit();
        if ($gameUnitEnum === null) {
            throw new \RuntimeException('Naval Bombardment requires a game unit');
        }
        $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
        return $gameUnit->getBattleStats()->getGroundBattleStats()->getAttack();
    }

    public function processFailed(): void
    {
        // Bombardment always succeeds — this should never be reached
    }

    public function processPostOperation(): void
    {
        $player = $this->getTargetRegionPlayer();
        $player->getNotifications()->setAttacked(true);
        $this->playerRepository->save($player);
    }
}
