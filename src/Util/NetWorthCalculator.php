<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;

final class NetWorthCalculator
{
    public const int NET_WORTH_CALCULATOR_REGION = 1000;

    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;

    public function __construct(
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository
    ) {
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
    }

    public function calculateNetWorthForPlayer(Player $player): int
    {
        $netWorth = 0;
        $netWorth += count($player->getWorldRegions()) * NetWorthCalculator::NET_WORTH_CALCULATOR_REGION;
        $netWorth += $this->getNetWorthFromWorldRegionStackableUnits($player);
        $netWorth += $this->getNetWorthFromWorldRegionLeveledUnits($player);
        $netWorth += $this->getNetWorthFromResearch($player);

        return $netWorth;
    }

    private function getNetWorthFromWorldRegionStackableUnits(Player $player): int
    {
        $netWorth = 0;
        foreach ($this->worldRegionStackableUnitRepository->findAmountAndNetWorthByPlayer($player) as $data) {
            $netWorth += $data['netWorth'] * $data['amount'];
        }


        return $netWorth;
    }

    private function getNetWorthFromWorldRegionLeveledUnits(Player $player): int
    {
        $netWorth = 0;
        foreach ($this->worldRegionLeveledUnitRepository->findLevelAndNetWorthByPlayer($player) as $data) {
            $netWorth += $data['netWorth'] * $data['level'];
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
