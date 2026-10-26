<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\FleetMovement;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\FleetActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\FleetRouteService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class FleetController extends BaseGameController
{
    private FleetActionService $fleetActionService;
    private RegionActionService $regionActionService;
    private GameUnitRegistry $gameUnitRegistry;
    private DistanceCalculator $distanceCalculator;
    private FleetRouteService $fleetRouteService;

    public function __construct(
        FleetActionService $fleetActionService,
        RegionActionService $regionActionService,
        GameUnitRegistry $gameUnitRegistry,
        DistanceCalculator $distanceCalculator,
        FleetRouteService $fleetRouteService
    ) {
        $this->fleetActionService = $fleetActionService;
        $this->regionActionService = $regionActionService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->distanceCalculator = $distanceCalculator;
        $this->fleetRouteService = $fleetRouteService;
    }

    public function fleetOverviewApi(): JsonResponse
    {
        $player = $this->getPlayer();
        $currentTime = time();
        $fleets = [];

        foreach ($player->getFleets() as $fleet) {
            $sourceRegion = $fleet->getWorldRegion();
            $targetRegion = $fleet->getTargetWorldRegion();
            $arriveTime = $fleet->getTimestampArrive();
            $hasArrived = $currentTime >= $arriveTime;
            $targetPlayer = $targetRegion->getPlayer();
            $targetIsYours = $targetPlayer !== null && $targetPlayer->getId() === $player->getId();

            $units = [];
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $gameUnit = $this->gameUnitRegistry->find($fleetUnit->getGameUnit());
                $units[] = [
                    'name' => $fleetUnit->getAmount() === 1 ? $gameUnit->getName() : $gameUnit->getNameMulti(),
                    'amount' => $fleetUnit->getAmount(),
                ];
            }

            $fleets[] = [
                'id' => $fleet->getId(),
                'sourceX' => $sourceRegion->getX(),
                'sourceY' => $sourceRegion->getY(),
                'targetX' => $targetRegion->getX(),
                'targetY' => $targetRegion->getY(),
                'targetOwner' => $targetPlayer !== null ? $targetPlayer->getName() : '',
                'targetIsYours' => $targetIsYours,
                'targetIsOpenSea' => $targetRegion->isWater() && $targetPlayer === null,
                'hasArrived' => $hasArrived,
                'timeLeft' => max(0, $arriveTime - $currentTime),
                'arriveAt' => date('Y-m-d H:i:s', $arriveTime),
                'units' => $units,
            ];
        }

        return new JsonResponse([
            'success' => true,
            'fleets' => $fleets,
        ]);
    }

    /**
     * API endpoint for recalling a fleet (JSON response)
     */
    public function recallApi(int $fleetId): JsonResponse
    {
        try {
            $region = $this->fleetActionService->recall($fleetId, $this->getPlayer());
            return new JsonResponse([
                'success' => true,
                'message' => 'You successfully recalled your troops!',
                'regionId' => $region->getId(),
                'units' => $this->gameUnitRegistry->getRegionUnitSummary($region),
                'newlyVisibleEnemyUnits' => $this->getNewlyVisibleEnemyUnits($region),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API endpoint for reinforcing a region (JSON response)
     */
    public function reinforceApi(int $fleetId): JsonResponse
    {
        try {
            $region = $this->fleetActionService->reinforce($fleetId, $this->getPlayer());
            return new JsonResponse([
                'success' => true,
                'message' => 'You successfully reinforced your region!',
                'regionId' => $region->getId(),
                'units' => $this->gameUnitRegistry->getRegionUnitSummary($region),
                'newlyVisibleEnemyUnits' => $this->getNewlyVisibleEnemyUnits($region),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Get sendable units from a player's region
     */
    public function sendUnitsDataApi(int $regionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        $units = [];
        foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() <= 0) {
                continue;
            }

            $gameUnitEnum = $worldRegionStackableUnit->getGameUnit();
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            $category = $gameUnit->getGameUnitCategory();

            if (!$category->isSendable()) {
                continue;
            }

            // Only movable unit categories
            if (
                !in_array($category, [
                GameUnitCategory::TROOPS,
                GameUnitCategory::AIR_UNITS,
                GameUnitCategory::NAVAL_UNITS,
                ], true)
            ) {
                continue;
            }

            $units[] = [
                'gameUnitId' => $gameUnitEnum->value,
                'name' => $gameUnit->getName(),
                'image' => $gameUnit->getImage(),
                'imageDir' => $category->getImageDir(),
                'amount' => $worldRegionStackableUnit->getAmount(),
                'category' => $category->getLabel(),
                'isNaval' => $category === GameUnitCategory::NAVAL_UNITS,
            ];
        }

        return new JsonResponse([
            'success' => true,
            'units' => $units,
            'sourceRegion' => [
                'id' => $playerRegion->getId(),
                'x' => $playerRegion->getX(),
                'y' => $playerRegion->getY(),
            ],
        ]);
    }

    /**
     * API: Get target regions (your own regions and open sea next to them) the selected units can reach.
     * Ground troops march through your own regions, ships sail over water and aircraft fly to an Airfield.
     */
    public function sendUnitsTargetsApi(Request $request, int $regionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $sourceRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        // Comma separated ids of the selected units, which decide how the fleet travels
        $gameUnits = [];
        foreach (explode(',', $request->query->getString('units')) as $gameUnitId) {
            $gameUnitEnum = is_numeric($gameUnitId) ? GameUnitEnum::tryFrom((int) $gameUnitId) : null;
            if ($gameUnitEnum !== null) {
                $gameUnits[] = $this->gameUnitRegistry->find($gameUnitEnum);
            }
        }

        if ($gameUnits === []) {
            return new JsonResponse(['success' => false, 'message' => 'No units selected!'], 400);
        }

        try {
            $movement = $this->fleetRouteService->getMovement($gameUnits);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }

        // Ships can also sail to open sea next to one of your regions, which claims it on arrival
        $candidates = $player->getWorldRegions()->toArray();
        if ($movement === FleetMovement::NAVAL) {
            $candidates = [...$candidates, ...$this->regionActionService->getOpenSeaRegionsNextToPlayer($player)];
        }

        $routeSteps = $this->fleetRouteService->findTargetRegions($sourceRegion, $player, $movement);

        $targets = [];
        foreach ($candidates as $worldRegion) {
            if ($worldRegion->getId() === $sourceRegion->getId()) {
                continue; // Can't send to self
            }

            // Travel time depends on the units sent, so only the distance is known here
            if ($movement === FleetMovement::AIR) {
                if ($worldRegion->getLeveledUnit(GameUnitEnum::AIRFIELD) === null) {
                    continue; // Aircraft only land on an Airfield
                }

                $distance = $this->distanceCalculator->calculateDistance(
                    $worldRegion->getX(),
                    $worldRegion->getY(),
                    $sourceRegion->getX(),
                    $sourceRegion->getY()
                );
            } else {
                $steps = $routeSteps[$worldRegion->getId()] ?? null;
                if ($steps === null) {
                    continue; // Not connected to the source region
                }

                $distance = 2 * $steps;
            }

            $targets[] = [
                'regionId' => $worldRegion->getId(),
                'x' => $worldRegion->getX(),
                'y' => $worldRegion->getY(),
                'distance' => $distance,
                'isWater' => $worldRegion->isWater(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'sourceRegionId' => $regionId,
            'targets' => $targets,
        ]);
    }

    /**
     * API: Send units from one of your regions to another (creates fleet)
     */
    public function sendUnitsApi(Request $request, int $regionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $sourceRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($request->getContent(), true);
        $rawTargetId = is_array($data) ? ($data['targetRegionId'] ?? null) : null;
        $targetRegionId = is_numeric($rawTargetId) ? (int) $rawTargetId : 0;
        /** @var array<string, int|string> $unitData */
        $unitData = is_array($data) && isset($data['units']) && is_array($data['units']) ? $data['units'] : [];

        if ($unitData === []) {
            return new JsonResponse(['success' => false, 'message' => 'No units selected!'], 400);
        }

        if ($targetRegionId === 0) {
            return new JsonResponse(['success' => false, 'message' => 'No target region selected!'], 400);
        }

        try {
            $targetRegion = $this->regionActionService->getWorldRegionByIdAndWorld(
                $targetRegionId,
                $player->getWorld()
            );
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => 'Target region not found or not yours!'], 404);
        }

        // Units move to your own regions, or ships to open sea (unowned water)
        $targetPlayer = $targetRegion->getPlayer();
        $targetIsOpenSea = $targetRegion->isWater() && $targetPlayer === null;
        if ($targetPlayer?->getId() !== $player->getId() && !$targetIsOpenSea) {
            return new JsonResponse(['success' => false, 'message' => 'Target region not found or not yours!'], 404);
        }

        // Convert unit data to string values expected by FleetActionService
        $filteredUnits = [];
        foreach ($unitData as $gameUnitId => $amount) {
            if (!is_numeric($amount)) {
                continue;
            }
            $amountInt = (int) $amount;
            if ($amountInt > 0) {
                $filteredUnits[(int) $gameUnitId] = (string) $amountInt;
            }
        }

        if ($filteredUnits === []) {
            return new JsonResponse(['success' => false, 'message' => 'No valid units selected!'], 400);
        }

        try {
            $fleet = $this->fleetActionService->sendGameUnits(
                $sourceRegion,
                $targetRegion,
                $player,
                $filteredUnits
            );
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $sentUnits = [];
        $totalUnitCount = 0;
        foreach ($filteredUnits as $gameUnitId => $amount) {
            $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitId);
            if ($gameUnitEnum === null) {
                continue;
            }
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            $sentUnits[] = [
                'name' => $gameUnit->getName(),
                'amount' => (int) $amount,
            ];
            $totalUnitCount += (int) $amount;
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Units dispatched successfully!',
            'fleet' => [
                'id' => $fleet->getId(),
                'sourceX' => $sourceRegion->getX(),
                'sourceY' => $sourceRegion->getY(),
                'targetX' => $targetRegion->getX(),
                'targetY' => $targetRegion->getY(),
                'path' => $fleet->getPath(),
                'targetRegionId' => $targetRegion->getId(),
                'targetIsYours' => !$targetIsOpenSea,
                'targetIsOpenSea' => $targetIsOpenSea,
                'timestamp' => $fleet->getTimestamp(),
                'timestampArrive' => $fleet->getTimestampArrive(),
                'hasArrived' => false,
                'eta' => max(0, $fleet->getTimestampArrive() - time()),
                'unitCount' => $totalUnitCount,
                'units' => $sentUnits,
            ],
            'sourceRegionId' => $sourceRegion->getId(),
            'sourceRegionUnits' => $this->gameUnitRegistry->getRegionUnitSummary($sourceRegion),
            // A water region is released when its last units leave
            'sourceRegionReleased' => $sourceRegion->getPlayer() === null,
        ]);
    }

    /**
     * Masked units of enemy neighbors, revealed when a fleet claims a water region
     *
     * @return array<int, array<string, mixed>>
     */
    private function getNewlyVisibleEnemyUnits(WorldRegion $region): array
    {
        if (!$region->isWater()) {
            return [];
        }

        return $this->regionActionService->getNeighborEnemyUnitPresence($region, $this->getPlayer());
    }
}
