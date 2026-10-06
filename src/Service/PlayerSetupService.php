<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use RuntimeException;

final class PlayerSetupService
{
    private WorldRegionRepository $worldRegionRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private DistanceCalculator $distanceCalculator;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private IncomeUpdaterService $incomeUpdaterService;

    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        DistanceCalculator $distanceCalculator,
        NetWorthUpdaterService $netWorthUpdaterService,
        IncomeUpdaterService $incomeUpdaterService
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->distanceCalculator = $distanceCalculator;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->incomeUpdaterService = $incomeUpdaterService;
    }

    public function setupNewPlayer(Player $player, World $world): void
    {
        $region = $this->findStartingRegion($world);

        $region->setPlayer($player);
        $player->getWorldRegions()->add($region);
        $this->worldRegionRepository->save($region);

        $this->createStartingUnits($region);

        $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
        $this->incomeUpdaterService->updateIncomeForPlayer($player);
    }

    private function findStartingRegion(World $world): WorldRegion
    {
        $waterTypes = [
            WorldRegion::TYPE_DEEP_WATER,
            WorldRegion::TYPE_WATER,
            WorldRegion::TYPE_SHALLOW_WATER,
        ];

        $unownedRegions = $this->worldRegionRepository->findByWorldAndPlayer($world, null);

        $landRegions = [];
        foreach ($unownedRegions as $region) {
            if (!in_array($region->getType(), $waterTypes, true)) {
                $landRegions[] = $region;
            }
        }

        if ($landRegions === []) {
            throw new RuntimeException('No available land regions in this world!');
        }

        $ownedRegions = [];
        foreach ($world->getWorldRegions() as $region) {
            if ($region->getPlayer() !== null) {
                $ownedRegions[] = $region;
            }
        }

        if ($ownedRegions === []) {
            return $landRegions[0];
        }

        $bestRegion = $landRegions[0];
        $bestMinDistance = 0;

        foreach ($landRegions as $candidate) {
            $minDistance = PHP_INT_MAX;

            foreach ($ownedRegions as $owned) {
                $distance = $this->distanceCalculator->calculateDistance(
                    $candidate->getX(),
                    $candidate->getY(),
                    $owned->getX(),
                    $owned->getY()
                );

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                }
            }

            if ($minDistance > $bestMinDistance) {
                $bestMinDistance = $minDistance;
                $bestRegion = $candidate;
            }
        }

        return $bestRegion;
    }

    private function createStartingUnits(WorldRegion $region): void
    {
        $startingUnits = [
            [GameUnitEnum::ECONOMIC_CENTER, 1000],
            [GameUnitEnum::FARM, 100],
            [GameUnitEnum::IRON_MINE, 100],
            [GameUnitEnum::WOODCUTTER, 250],
            [GameUnitEnum::SOLDIER, 10],
        ];

        foreach ($startingUnits as [$gameUnit, $amount]) {
            $worldRegionStackableUnit = WorldRegionStackableUnit::create($region, $gameUnit, $amount);
            $this->worldRegionStackableUnitRepository->save($worldRegionStackableUnit);
        }
    }
}
