<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use RuntimeException;

/**
 * @property AbstractGameResources $abstractGameResources
 */
abstract class AbstractPlayerCalculator
{
    protected const ABSTRACT_GAME_RESOURCES_UPKEEP = 'upkeep';
    protected const ABSTRACT_GAME_RESOURCES_INCOME = 'income';

    protected AbstractGameResources $abstractGameResources;

    protected function calculateForFleets(Player $player, string $type): void
    {
        foreach ($player->getFleets() as $fleet) {
            $this->calculateForFleetUnits($fleet, $type);
        }
    }

    private function calculateForFleetUnits(Fleet $fleet, string $type): void
    {
        foreach ($fleet->getFleetUnits() as $fleetUnit) {
            $gameUnitResource = $this->getAbstractGameResources($fleetUnit->getGameUnit(), $type);
            $this->updateAbstractGameResource($fleetUnit->getAmount(), $gameUnitResource);
        }
    }

    protected function calculateForWorldRegions(Player $player, string $type): void
    {
        foreach ($player->getWorldRegions() as $worldRegion) {
            $this->calculateForWorldRegionUnits($worldRegion, $type);
        }
    }

    private function calculateForWorldRegionUnits(WorldRegion $worldRegion, string $type): void
    {
        foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
            $gameUnitResource = $this->getAbstractGameResources($worldRegionUnit->getGameUnit(), $type);
            $this->updateAbstractGameResource($worldRegionUnit->getAmount(), $gameUnitResource);
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
