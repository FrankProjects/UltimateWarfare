<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;

final class IncomeCalculator
{
    /**
     * @var Player\Income
     */
    private $income;

    /**
     * IncomeCalculator constructor.
     */
    public function __construct()
    {
        $this->income = new Player\Income();
    }

    /**
     * @param Player $player
     * @return Player\Income
     */
    public function calculateIncomeForPlayer(Player $player): Player\Income
    {
        $this->calculateIncomeForFleets($player);
        $this->calculateIncomeForWorldRegionUnits($player);

        return $this->income;
    }

    /**
     * @param Player $player
     */
    private function calculateIncomeForFleets(Player $player): void
    {
        foreach ($player->getFleets() as $fleet) {
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $this->income->addCash($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeCash());
                $this->income->addFood($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeFood());
                $this->income->addWood($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeWood());
                $this->income->addSteel($fleetUnit->getAmount() * $fleetUnit->getGameUnit()->getIncomeSteel());
            }
        }
    }

    /**
     * @param Player $player
     */
    private function calculateIncomeForWorldRegionUnits(Player $player): void
    {
        foreach ($player->getWorldRegions() as $worldRegion) {
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                $this->income->addCash($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getIncomeCash());
                $this->income->addFood($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getIncomeFood());
                $this->income->addWood($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getIncomeWood());
                $this->income->addSteel($worldRegionUnit->getAmount() * $worldRegionUnit->getGameUnit()->getIncomeSteel());
            }
        }
    }
}
