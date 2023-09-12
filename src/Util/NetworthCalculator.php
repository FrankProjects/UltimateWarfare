<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;

final class NetworthCalculator
{
    public const NETWORTH_CALCULATOR_REGION = 1000;

    private WorldRegionUnitRepository $worldRegionUnitRepository;

    public function __construct(
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    public function calculateNetworthForPlayer(Player $player): int
    {
        $networth = 0;
        $networth += count($player->getWorldRegions()) * NetworthCalculator::NETWORTH_CALCULATOR_REGION;
        $networth += $this->getNetworthFromWorldRegionUnits($player);
        $networth += $this->getNetworthFromResearch($player);

        return $networth;
    }

    private function getNetworthFromWorldRegionUnits(Player $player): int
    {
        $networth = 0;
        foreach ($this->worldRegionUnitRepository->findAmountAndNetworthByPlayer($player) as $data) {
            $networth += $data['networth'] * $data['amount'];
        }


        return $networth;
    }

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
