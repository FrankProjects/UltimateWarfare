<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;

final class NetworthCalculator
{
    const NETWORTH_CALCULATOR_REGION = 1000;

    /**
     * @var WorldRegionUnitRepository
     */
    private $worldRegionUnitRepository;

    /**
     * NetworthCalculator constructor.
     *
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     */
    public function __construct(
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    /**
     * @param Player $player
     * @return int
     */
    public function calculateNetworthForPlayer(Player $player): int
    {
        $networth = 0;
        $networth += count($player->getWorldRegions()) * NetworthCalculator::NETWORTH_CALCULATOR_REGION;
        $networth += $this->getNetworthFromWorldRegionUnits($player);
        $networth += $this->getNetworthFromResearch($player);

        return $networth;
    }

    /**
     * @param Player $player
     * @return int
     */
    private function getNetworthFromWorldRegionUnits(Player $player): int
    {
        $networth = 0;

        foreach ($this->worldRegionUnitRepository->findAmountAndNetworthByPlayer($player) as $data) {
            $networth += $data['networth'] * $data['amount'];
        }

        return $networth;
    }

    /**
     * @param Player $player
     * @return int
     */
    private function getNetworthFromResearch(Player $player): int
    {
        $networth = 0;

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
