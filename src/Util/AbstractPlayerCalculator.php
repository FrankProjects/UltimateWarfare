<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use RuntimeException;

/**
 * @property AbstractGameResources $abstractGameResources
 */
abstract class AbstractPlayerCalculator
{
    protected const string ABSTRACT_GAME_RESOURCES_UPKEEP = 'upkeep';
    protected const string ABSTRACT_GAME_RESOURCES_INCOME = 'income';

    protected AbstractGameResources $abstractGameResources;

    public function __construct(
        private readonly GameUnitRegistry $gameUnitRegistry
    ) {
    }

    protected function calculateForFleets(Player $player, string $type): void
    {
        foreach ($player->getFleets() as $fleet) {
            $this->calculateForFleetUnits($fleet, $type);
        }
    }

    private function calculateForFleetUnits(Fleet $fleet, string $type): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $gameUnit = $this->gameUnitRegistry->find($fleetUnit->getGameUnit());
            $gameUnitResource = $this->getAbstractGameResources($gameUnit, $type);
            $this->updateAbstractGameResource($fleetUnit->getAmount(), $gameUnitResource);
        }
    }

    protected function calculateForWorldRegions(Player $player, string $type): void
    {
        foreach ($player->getWorldRegions() as $worldRegion) {
            $this->calculateForWorldRegionStackableUnits($worldRegion, $type);
        }
    }

    private function calculateForWorldRegionStackableUnits(WorldRegion $worldRegion, string $type): void
    {
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            $gameUnitResource = $this->getAbstractGameResources($gameUnit, $type);
            $this->updateAbstractGameResource($worldRegionStackableUnit->getAmount(), $gameUnitResource);
        }

        // Leveled buildings (Defense / Special) scale their income/upkeep by their level.
        foreach ($worldRegion->getWorldRegionLeveledUnits() as $leveledUnit) {
            $gameUnit = $this->gameUnitRegistry->find($leveledUnit->getGameUnit());
            $gameUnitResource = $this->getAbstractGameResources($gameUnit, $type);
            $this->updateAbstractGameResource($leveledUnit->getLevel(), $gameUnitResource);
        }
    }

    private function updateAbstractGameResource(int $amount, AbstractGameResources $abstractGameResources): void
    {
        $this->abstractGameResources->addCash($amount * $abstractGameResources->getCash());
        $this->abstractGameResources->addFood($amount * $abstractGameResources->getFood());
        $this->abstractGameResources->addWood($amount * $abstractGameResources->getWood());
        $this->abstractGameResources->addSteel($amount * $abstractGameResources->getSteel());
    }

    private function getAbstractGameResources(GameUnit $gameUnit, string $type): AbstractGameResources
    {
        if ($type === AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_UPKEEP) {
            return $gameUnit->getUpkeep();
        } elseif ($type === AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME) {
            return $gameUnit->getIncome();
        }

        throw new RuntimeException("Invalid AbstractGameResource type {$type}");
    }
}
