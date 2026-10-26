<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use FrankProjects\UltimateWarfare\Service\PopulationUpdaterService;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;

final class StatisticsController extends BaseGameController
{
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private ResourceAccrualCalculator $resourceAccrualCalculator;
    private PlayerStateUpdaterService $playerStateUpdaterService;
    private MessageRepository $messageRepository;
    private WorldRegionRepository $worldRegionRepository;

    public function __construct(
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        GameUnitRegistry $gameUnitRegistry,
        ResourceAccrualCalculator $resourceAccrualCalculator,
        PlayerStateUpdaterService $playerStateUpdaterService,
        MessageRepository $messageRepository,
        WorldRegionRepository $worldRegionRepository
    ) {
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->resourceAccrualCalculator = $resourceAccrualCalculator;
        $this->playerStateUpdaterService = $playerStateUpdaterService;
        $this->messageRepository = $messageRepository;
        $this->worldRegionRepository = $worldRegionRepository;
    }

    /**
     * Current resources and rates for the live resource ticker, plus when they next change by a completion
     */
    public function resourcesApi(): JsonResponse
    {
        $player = $this->getPlayer();
        $now = time();
        $resources = $this->resourceAccrualCalculator->project($player, $now);
        $income = $player->getIncome();
        $upkeep = $player->getUpkeep();

        return new JsonResponse([
            'success' => true,
            'serverTime' => $now,
            'resources' => [
                'cash' => $resources->getCash(),
                'wood' => $resources->getWood(),
                'food' => $resources->getFood(),
                'steel' => $resources->getSteel(),
            ],
            'rates' => [
                'cash' => $income->getCash() - $upkeep->getCash(),
                'wood' => $income->getWood() - $upkeep->getWood(),
                'food' => $income->getFood() - $upkeep->getFood(),
                'steel' => $income->getSteel() - $upkeep->getSteel(),
            ],
            'nextCompletion' => $this->playerStateUpdaterService->getNextCompletionTimestamp($player),
            'unreadMessages' => $this->messageRepository->countUnreadMessagesToPlayer($player),
        ]);
    }

    public function statisticsApi(): JsonResponse
    {
        $player = $this->getPlayer();

        $income = $player->getIncome();
        $upkeep = $player->getUpkeep();
        $resources = $this->resourceAccrualCalculator->project($player, time());

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
                    'population' => $this->worldRegionRepository->getPopulationSumByPlayer($player),
                    'maxPopulation' => ($infraData[GameUnitEnum::HOUSE->value] ?? 0)
                        * PopulationUpdaterService::POPULATION_PER_HOUSE,
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
