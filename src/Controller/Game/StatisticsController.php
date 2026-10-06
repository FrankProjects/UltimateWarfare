<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

final class StatisticsController extends BaseGameController
{
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    public function statisticsApi(): JsonResponse
    {
        $player = $this->getPlayer();

        $income = $player->getIncome();
        $upkeep = $player->getUpkeep();
        $resources = $player->getResources();

        $warnings = [];
        $netFood = $income->getFood() - $upkeep->getFood();
        if ($netFood < 0 && ($resources->getFood() + $netFood) < 0) {
            $seconds = $resources->getFood() > 0
                ? (int) (($resources->getFood() / abs($netFood)) * 3600)
                : 0;
            $warnings[] = [
                'type' => 'food',
                'seconds' => $seconds,
            ];
        }

        $netCash = $income->getCash() - $upkeep->getCash();
        if ($netCash < 0 && ($resources->getCash() + $netCash) < 0) {
            $seconds = $resources->getCash() > 0
                ? (int) (($resources->getCash() / abs($netCash)) * 3600)
                : 0;
            $warnings[] = [
                'type' => 'cash',
                'seconds' => $seconds,
            ];
        }

        $armyCategories = [
            GameUnitCategory::TROOPS,
            GameUnitCategory::AIR_UNITS,
            GameUnitCategory::NAVAL_UNITS,
            GameUnitCategory::MISSILES,
        ];

        $infraCategories = [
            GameUnitCategory::BUILDINGS,
            GameUnitCategory::DEFENSE_BUILDINGS,
            GameUnitCategory::SPECIAL_BUILDINGS,
        ];

        $armyData = $this->worldRegionStackableUnitRepository->getGameUnitSumByPlayerAndGameUnitCategories(
            $player,
            $armyCategories
        );

        $infraData = $this->worldRegionStackableUnitRepository->getGameUnitSumByPlayerAndGameUnitCategories(
            $player,
            $infraCategories
        );

        $gameUnits = $this->gameUnitRegistry->findAll();

        $army = [];
        foreach ($armyCategories as $category) {
            $units = [];
            foreach ($gameUnits as $gameUnit) {
                if ($gameUnit->getGameUnitCategory() !== $category) {
                    continue;
                }
                $enumValue = $gameUnit->getGameUnitEnum()->value;
                if (isset($armyData[$enumValue]) && $armyData[$enumValue] > 0) {
                    $units[] = [
                        'name' => $gameUnit->getNameMulti(),
                        'amount' => $armyData[$enumValue],
                    ];
                }
            }
            $army[] = [
                'category' => $category->getLabel(),
                'units' => $units,
            ];
        }

        $infrastructure = [];
        foreach ($infraCategories as $category) {
            $units = [];
            foreach ($gameUnits as $gameUnit) {
                if ($gameUnit->getGameUnitCategory() !== $category) {
                    continue;
                }
                $enumValue = $gameUnit->getGameUnitEnum()->value;
                if (isset($infraData[$enumValue]) && $infraData[$enumValue] > 0) {
                    $units[] = [
                        'name' => $gameUnit->getNameMulti(),
                        'amount' => $infraData[$enumValue],
                    ];
                }
            }
            $infrastructure[] = [
                'category' => $category->getLabel(),
                'units' => $units,
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'status' => [
                    'cash' => $resources->getCash(),
                    'wood' => $resources->getWood(),
                    'steel' => $resources->getSteel(),
                    'food' => $resources->getFood(),
                    'regions' => count($player->getWorldRegions()),
                    'netWorth' => $player->getNetWorth(),
                ],
                'income' => [
                    'cashIncome' => $income->getCash(),
                    'cashUpkeep' => $upkeep->getCash(),
                    'cashNet' => $income->getCash() - $upkeep->getCash(),
                    'foodProduction' => $income->getFood(),
                    'foodConsumption' => $upkeep->getFood(),
                    'foodNet' => $income->getFood() - $upkeep->getFood(),
                    'steelIncome' => $income->getSteel(),
                    'woodIncome' => $income->getWood(),
                ],
                'warnings' => $warnings,
                'army' => $army,
                'infrastructure' => $infrastructure,
            ],
        ]);
    }
}
