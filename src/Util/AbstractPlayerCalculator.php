<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use RuntimeException;

abstract class AbstractPlayerCalculator
{
    const ABSTRACT_GAME_RESOURCES_UPKEEP = 'upkeep';
    const ABSTRACT_GAME_RESOURCES_INCOME = 'income';

    /**
     * @var AbstractGameResources
     */
    protected $abstractGameResources;

    /**
     * @param Player $player
     * @param string $type
     */
    protected function calculateForFleets(Player $player, string $type): void
    {
        foreach ($player->getFleets() as $fleet) {
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $gameUnitResource = $this->getAbstractGameResources($fleetUnit->getGameUnit(), $type);

                $this->abstractGameResources->addCash($fleetUnit->getAmount() * $gameUnitResource->getCash());
                $this->abstractGameResources->addFood($fleetUnit->getAmount() * $gameUnitResource->getFood());
                $this->abstractGameResources->addWood($fleetUnit->getAmount() * $gameUnitResource->getWood());
                $this->abstractGameResources->addSteel($fleetUnit->getAmount() * $gameUnitResource->getSteel());
            }
        }
    }

    /**
     * @param Player $player
     * @param string $type
     */
    protected function calculateForWorldRegionUnits(Player $player, string $type): void
    {
        foreach ($player->getWorldRegions() as $worldRegion) {
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                $gameUnitResource = $this->getAbstractGameResources($worldRegionUnit->getGameUnit(), $type);

                $this->abstractGameResources->addCash($worldRegionUnit->getAmount() * $gameUnitResource->getCash());
                $this->abstractGameResources->addFood($worldRegionUnit->getAmount() * $gameUnitResource->getFood());
                $this->abstractGameResources->addWood($worldRegionUnit->getAmount() * $gameUnitResource->getWood());
                $this->abstractGameResources->addSteel($worldRegionUnit->getAmount() * $gameUnitResource->getSteel());
            }
        }
    }

    /**
     * @param GameUnit $gameUnit
     * @param string $type
     * @return AbstractGameResources
     */
    private function getAbstractGameResources(GameUnit $gameUnit, string $type): AbstractGameResources
    {
        if ($type === AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_UPKEEP) {
            return $gameUnit->getUpkeep();
        } elseif ($type === AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME) {
            return $gameUnit->getIncome();
        }

        throw new RunTimeException("Invalid AbstractGameResource type {$type}");
    }
}
