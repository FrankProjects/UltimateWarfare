<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;

final class UpkeepCalculator
{
    /**
     * @var Player\Upkeep
     */
    private $upkeep;

    /**
     * @param Player $player
     * @return Player\Upkeep
     */
    public function calculateUpkeepForPlayer(Player $player): Player\Upkeep
    {
        $this->upkeep = new Player\Upkeep();

        $this->calculateUpkeepForFleets($player);
        $this->calculateUpkeepForWorldRegionUnits($player);

        return $this->upkeep;
    }

    /**
     * @param Player $player
     */
    private function calculateUpkeepForFleets(Player $player): void
    {
        foreach ($player->getFleets() as $fleet) {
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $this->upkeep->addCash($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepCash());
                $this->upkeep->addFood($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepFood());
                $this->upkeep->addWood($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepWood());
                $this->upkeep->addSteel($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepSteel());
            }
        }
    }

    /**
     * @param Player $player
     */
    private function calculateUpkeepForWorldRegionUnits(Player $player): void
    {
        foreach ($player->getWorldRegions() as $worldRegion) {
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                $this->upkeep->addCash($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getUpkeepCash());
                $this->upkeep->addFood($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getUpkeepFood());
                $this->upkeep->addWood($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getUpkeepWood());
                $this->upkeep->addSteel($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getUpkeepSteel());
            }
        }
    }
}
