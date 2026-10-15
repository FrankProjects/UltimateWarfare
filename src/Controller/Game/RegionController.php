<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\PopulationUpdaterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

final class RegionController extends BaseGameController
{
    private RegionActionService $regionActionService;
    private WorldRegionRepository $worldRegionRepository;
    private ConstructionRepository $constructionRepository;
    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(
        RegionActionService $regionActionService,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->regionActionService = $regionActionService;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->constructionRepository = $constructionRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    public function buyApi(int $regionId): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $this->regionActionService->buyWorldRegion($regionId, $player);

            $boughtRegion = $this->worldRegionRepository->find($regionId);
            $newlyVisibleEnemyUnits = [];

            if ($boughtRegion !== null) {
                $newlyVisibleEnemyUnits = $this->regionActionService->getNeighborEnemyUnitPresence(
                    $boughtRegion,
                    $player
                );
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'You have bought a Region!',
                'newCash' => $player->getResources()->getCash(),
                'newRegionPrice' => $player->getRegionPrice(),
                'newlyVisibleEnemyUnits' => $newlyVisibleEnemyUnits,
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function regionOverviewApi(): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $regions = $player->getWorldRegions();

            // Bulk queries: 2 queries instead of N*16
            $unitsByRegion = $this->worldRegionRepository->getWorldGameUnitSumByPlayer($player);
            $constructionsByRegion = $this->constructionRepository
                ->getGameUnitConstructionSumByPlayerGroupedByRegion($player);

            // Build category mapping: gameUnitEnum value -> category value
            $unitCategoryMap = $this->buildUnitCategoryMap();

            $regionList = [];
            foreach ($regions as $region) {
                $regionId = $region->getId();
                $regionUnits = $unitsByRegion[$regionId] ?? [];
                $regionConstructions = $constructionsByRegion[$regionId] ?? [];

                $categoryCounts = $this->aggregateByCategoryFromMaps(
                    $regionUnits,
                    $regionConstructions,
                    $unitCategoryMap
                );

                $regionList[] = [
                    'id' => $regionId,
                    'x' => $region->getX(),
                    'y' => $region->getY(),
                    'type' => $region->getType(),
                    'space' => $region->getSpace(),
                    'population' => $region->getPopulation(),
                    'maxPopulation' => ($regionUnits[GameUnitEnum::HOUSE->value] ?? 0)
                        * PopulationUpdaterService::POPULATION_PER_HOUSE,
                    'categoryCounts' => $categoryCounts,
                ];
            }

            return new JsonResponse([
                'success' => true,
                'regions' => $regionList,
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @return array<int, int>
     */
    private function buildUnitCategoryMap(): array
    {
        $map = [];
        foreach (GameUnitCategory::getAll() as $category) {
            foreach ($this->gameUnitRegistry->getIdsByCategory($category) as $gameUnitEnum) {
                $map[$gameUnitEnum->value] = $category->value;
            }
        }

        return $map;
    }

    /**
     * @param array<int, int> $regionUnits
     * @param array<int, int> $regionConstructions
     * @param array<int, int> $unitCategoryMap
     * @return array<int, array{count: int, inConstruction: int}>
     */
    private function aggregateByCategoryFromMaps(
        array $regionUnits,
        array $regionConstructions,
        array $unitCategoryMap
    ): array {
        /** @var array<int, array{count: int, inConstruction: int}> $categoryCounts */
        $categoryCounts = [];
        foreach (GameUnitCategory::getAll() as $category) {
            $categoryCounts[$category->value] = [
                'count' => 0,
                'inConstruction' => 0,
            ];
        }

        foreach ($regionUnits as $gameUnitValue => $amount) {
            $categoryValue = $unitCategoryMap[$gameUnitValue] ?? null;
            if ($categoryValue !== null && isset($categoryCounts[$categoryValue])) {
                $categoryCounts[$categoryValue]['count'] += $amount;
            }
        }

        foreach ($regionConstructions as $gameUnitValue => $amount) {
            $categoryValue = $unitCategoryMap[$gameUnitValue] ?? null;
            if ($categoryValue !== null && isset($categoryCounts[$categoryValue])) {
                $categoryCounts[$categoryValue]['inConstruction'] += $amount;
            }
        }

        return $categoryCounts;
    }
}
