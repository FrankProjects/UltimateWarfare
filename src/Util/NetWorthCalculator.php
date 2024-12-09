<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;

final class NetWorthCalculator
{
    public const int NET_WORTH_CALCULATOR_REGION = 1000;

    private WorldRegionUnitRepository $worldRegionUnitRepository;

    public function __construct(
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    public function calculateNetWorthForPlayer(Player $player): int
    {
        $netWorth = 0;
        $netWorth += count($player->getWorldRegions()) * NetWorthCalculator::NET_WORTH_CALCULATOR_REGION;
        $netWorth += $this->getNetWorthFromWorldRegionUnits($player);
        $netWorth += $this->getNetWorthFromResearch($player);

        return $netWorth;
    }

    private function getNetWorthFromWorldRegionUnits(Player $player): int
    {
        $netWorth = 0;
        foreach ($this->worldRegionUnitRepository->findAmountAndNetWorthByPlayer($player) as $data) {
            $netWorth += $data['netWorth'] * $data['amount'];
        }


        return $netWorth;
    }

    private function getNetWorthFromResearch(Player $player): int
    {
        $netWorth = 0;

        /** @var ResearchPlayer $playerResearch */
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if (!$playerResearch->getActive()) {
                continue;
            }

            $netWorth += 1250;
        }

        return $netWorth;
    }
}
