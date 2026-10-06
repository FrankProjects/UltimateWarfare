<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\LeveledUnitActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\RegionBuildDataService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

/**
 * API endpoints for leveled buildings (Defense / Special). Build and upgrade are timed;
 * repair is instant. Each endpoint only accepts leveled categories; stackable units must
 * use the construction endpoints.
 */
final class LeveledUnitController extends BaseGameController
{
    private RegionActionService $regionActionService;
    private LeveledUnitActionService $leveledUnitActionService;
    private GameUnitRegistry $gameUnitRegistry;
    private RegionBuildDataService $regionBuildDataService;

    public function __construct(
        RegionActionService $regionActionService,
        LeveledUnitActionService $leveledUnitActionService,
        GameUnitRegistry $gameUnitRegistry,
        RegionBuildDataService $regionBuildDataService
    ) {
        $this->regionActionService = $regionActionService;
        $this->leveledUnitActionService = $leveledUnitActionService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->regionBuildDataService = $regionBuildDataService;
    }

    public function buildApi(int $regionId, int $gameUnitEnumId): JsonResponse
    {
        return $this->handle($regionId, $gameUnitEnumId, 'build', 'is now being built!');
    }

    public function upgradeApi(int $regionId, int $gameUnitEnumId): JsonResponse
    {
        return $this->handle($regionId, $gameUnitEnumId, 'upgrade', 'is now being upgraded!');
    }

    public function repairApi(int $regionId, int $gameUnitEnumId): JsonResponse
    {
        return $this->handle($regionId, $gameUnitEnumId, 'repair', 'has been repaired!');
    }

    private function handle(int $regionId, int $gameUnitEnumId, string $action, string $successSuffix): JsonResponse
    {
        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitEnumId);
        if ($gameUnitEnum === null) {
            return new JsonResponse(['success' => false, 'message' => 'Unknown GameUnit!'], 400);
        }

        try {
            $player = $this->getPlayer();
            match ($action) {
                'build' => $this->leveledUnitActionService->build($worldRegion, $player, $gameUnitEnum),
                'upgrade' => $this->leveledUnitActionService->upgrade($worldRegion, $player, $gameUnitEnum),
                'repair' => $this->leveledUnitActionService->repair($worldRegion, $player, $gameUnitEnum),
            };

            $unitName = $this->gameUnitRegistry->find($gameUnitEnum)->getName();

            return new JsonResponse([
                'success' => true,
                'message' => "{$unitName} {$successSuffix}",
                'newCash' => $player->getResources()->getCash(),
                'newWood' => $player->getResources()->getWood(),
                'newSteel' => $player->getResources()->getSteel(),
                'buildData' => $this->regionBuildDataService->getBuildData($worldRegion, $player),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
