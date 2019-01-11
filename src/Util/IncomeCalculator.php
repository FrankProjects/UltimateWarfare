<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;

final class IncomeCalculator
{
    /**
     * @param Player $player
     * @return Player\Resources
     */
    public function calculateIncomeForPlayer(Player $player): Player\Resources
    {
        $upkeepCash = 0;
        $upkeepFood = 0;
        $upkeepWood = 0;
        $upkeepSteel = 0;

        $incomeCash = 0;
        $incomeFood = 0;
        $incomeWood = 0;
        $incomeSteel = 0;

        foreach ($player->getFleets() as $fleet) {
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $upkeepCash += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepCash();
                $upkeepFood += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepFood();
                $upkeepWood += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepWood();
                $upkeepSteel += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getUpkeepSteel();

                $incomeCash += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeCash();
                $incomeFood += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeFood();
                $incomeWood += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeWood();
                $incomeSteel += $fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeSteel();
            }
        }

        foreach ($player->getWorldRegions() as $worldRegion) {
            foreach ($worldRegion->getWorldRegionUnits() as $worldRgionUnit) {
                $upkeepCash += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepCash();
                $upkeepFood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepFood();
                $upkeepWood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepWood();
                $upkeepSteel += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepSteel();

                $incomeCash += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeCash();
                $incomeFood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeFood();
                $incomeWood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeWood();
                $incomeSteel += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeSteel();
            }
        }

        $resources = clone $player->getResources();

        $resources->setUpkeepCash($upkeepCash);
        $resources->setUpkeepFood($upkeepFood);
        $resources->setUpkeepWood($upkeepWood);
        $resources->setUpkeepSteel($upkeepSteel);

        $resources->setIncomeCash($incomeCash);
        $resources->setIncomeFood($incomeFood);
        $resources->setIncomeWood($incomeWood);
        $resources->setIncomeSteel($incomeSteel);

        return $resources;
    }
}
