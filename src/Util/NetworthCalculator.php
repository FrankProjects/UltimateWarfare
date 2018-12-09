<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

final class NetworthCalculator
{
    /**
     * @param Player $player
     * @return int
     */
    public static function calculateNetworthForPlayer(Player $player): int
    {
        $networth = 0;
        $networth += count($player->getWorldRegions()) * 1000;

        foreach ($player->getWorldRegions() as $worldRegion) {
            /** @var WorldRegion $worldRegion */
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                $networth += $worldRegionUnit->getGameUnit()->getNetworth() * $worldRegionUnit->getAmount();
            }
        }

        /** @var ResearchPlayer $playerResearch */
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if (!$playerResearch->getActive()) {
                continue;
            }

            $networth += 1250;
        }

        return $networth;
    }
}
