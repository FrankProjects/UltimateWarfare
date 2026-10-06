<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\ConstructionActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\RegionBuildDataService;
use Symfony\Component\HttpFoundation\Request;
use Throwable;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ConstructionController extends BaseGameController
{
    private ConstructionRepository $constructionRepository;
    private ConstructionActionService $constructionActionService;
    private RegionActionService $regionActionService;
    private GameUnitRegistry $gameUnitRegistry;
    private RegionBuildDataService $regionBuildDataService;

    public function __construct(
        ConstructionRepository $constructionRepository,
        ConstructionActionService $constructionActionService,
        RegionActionService $regionActionService,
        GameUnitRegistry $gameUnitRegistry,
        RegionBuildDataService $regionBuildDataService
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->constructionActionService = $constructionActionService;
        $this->regionActionService = $regionActionService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->regionBuildDataService = $regionBuildDataService;
    }

    public function constructionOverviewApi(): JsonResponse
    {
        $constructions = $this->constructionRepository->findByPlayer($this->getPlayer());
        $items = [];

        foreach ($constructions as $construction) {
            $gameUnit = $this->gameUnitRegistry->find($construction->getGameUnit());
            $unitName = $construction->getNumber() === 1
                ? $gameUnit->getName()
                : $gameUnit->getNameMulti();
            $timeLeft = ($construction->getTimestamp() + $construction->getDuration()) - time();

            $items[] = [
                'id' => $construction->getId(),
                'unitName' => $unitName,
                'number' => $construction->getNumber(),
                'regionId' => $construction->getWorldRegion()->getId(),
                'regionX' => $construction->getWorldRegion()->getX(),
                'regionY' => $construction->getWorldRegion()->getY(),
                'timeLeft' => max(0, $timeLeft),
                'categoryId' => $gameUnit->getGameUnitCategory()->value,
                'categoryName' => $gameUnit->getGameUnitCategory()->getLabel(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'constructions' => $items,
        ]);
    }

    public function cancelApi(int $constructionId): JsonResponse
    {
        try {
            $worldRegion = $this->constructionActionService->cancelConstruction($this->getPlayer(), $constructionId);
            return new JsonResponse([
                'success' => true,
                'message' => 'Successfully cancelled construction queue!',
                'buildData' => $this->regionBuildDataService->getBuildData($worldRegion, $this->getPlayer()),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function constructGameUnitsApi(Request $request, int $regionId, int $gameUnitCategoryId): JsonResponse
    {
        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        $gameUnitCategory = GameUnitCategory::fromInteger($gameUnitCategoryId);
        if ($gameUnitCategory === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unknown GameUnitCategory!'
            ], 400);
        }

        try {
            /** @var array<string, mixed>|null $data */
            $data = json_decode($request->getContent(), true);
            /** @var array<int, string> $construct */
            $construct = is_array($data) && isset($data['construct']) && is_array($data['construct'])
                ? $data['construct']
                : [];

            $this->constructionActionService->constructGameUnits(
                $worldRegion,
                $this->getPlayer(),
                $gameUnitCategory,
                $construct
            );

            $player = $this->getPlayer();
            $message = "New {$gameUnitCategory->getLabel()}"
                . " are now being {$gameUnitCategory->getConstructionAction()}!";

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'newCash' => $player->getResources()->getCash(),
                'newWood' => $player->getResources()->getWood(),
                'newSteel' => $player->getResources()->getSteel(),
                'buildData' => $this->regionBuildDataService->getBuildData($worldRegion, $player),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function removeGameUnitsApi(Request $request, int $regionId, int $gameUnitCategoryId): JsonResponse
    {
        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        $gameUnitCategory = GameUnitCategory::fromInteger($gameUnitCategoryId);
        if ($gameUnitCategory === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unknown GameUnitCategory!'
            ], 400);
        }

        try {
            /** @var array<string, mixed>|null $data */
            $data = json_decode($request->getContent(), true);
            /** @var array<int, string> $destroy */
            $destroy = is_array($data) && isset($data['destroy']) && is_array($data['destroy'])
                ? $data['destroy']
                : [];

            $this->constructionActionService->removeGameUnits(
                $worldRegion,
                $this->getPlayer(),
                $gameUnitCategory,
                $destroy
            );

            $action = $gameUnitCategory->getRemoveGameUnitActionDescription();
            $message = "You have {$action} {$gameUnitCategory->getLabel()}!";

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'buildData' => $this->regionBuildDataService->getBuildData($worldRegion, $this->getPlayer()),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllBuildDataApi(int $regionId): JsonResponse
    {
        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        return new JsonResponse(
            ['success' => true] + $this->regionBuildDataService->getBuildData($worldRegion, $this->getPlayer())
        );
    }
}
