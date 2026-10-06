<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\BombardmentCooldownRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\OperationRegistry;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\OperationService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class OperationController extends BaseGameController
{
    private OperationRegistry $operationRegistry;
    private GameUnitRegistry $gameUnitRegistry;
    private RegionActionService $regionActionService;
    private OperationService $operationService;
    private DistanceCalculator $distanceCalculator;
    private BombardmentCooldownRepository $bombardmentCooldownRepository;

    public function __construct(
        OperationRegistry $operationRegistry,
        GameUnitRegistry $gameUnitRegistry,
        RegionActionService $regionActionService,
        OperationService $operationService,
        DistanceCalculator $distanceCalculator,
        BombardmentCooldownRepository $bombardmentCooldownRepository
    ) {
        $this->operationRegistry = $operationRegistry;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->regionActionService = $regionActionService;
        $this->operationService = $operationService;
        $this->distanceCalculator = $distanceCalculator;
        $this->bombardmentCooldownRepository = $bombardmentCooldownRepository;
    }

    public function operationsApi(int $regionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        if ($worldRegion->getPlayer() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot target region without owner!'], 400);
        }

        if ($worldRegion->getPlayer()->getId() === $player->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot target your own region!'], 400);
        }

        $operations = $this->operationRegistry->findAvailableForPlayer($player);

        $operationsData = [];
        foreach ($operations as $operation) {
            $gameUnitEnum = $operation->getGameUnit();
            $gameUnit = $gameUnitEnum === null ? null : $this->gameUnitRegistry->find($gameUnitEnum);
            $operationsData[] = [
                'slug' => $operation->getSlug(),
                'name' => $operation->getName(),
                'image' => $operation->getImage(),
                'cost' => $operation->getCost(),
                'totalCost' => $operation->calculateCost($worldRegion, 1),
                'description' => $operation->getDescription(),
                'difficulty' => $operation->getDifficulty(),
                'maxDistance' => $operation->getMaxDistance(),
                'unitName' => $gameUnit?->getName(),
                'unitImage' => $gameUnit?->getImage(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'operations' => $operationsData,
        ]);
    }

    public function operationEligibleRegionsApi(int $regionId, string $operationSlug): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        if ($worldRegion->getPlayer() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot target region without owner!'], 400);
        }

        if ($worldRegion->getPlayer()->getId() === $player->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot target your own region!'], 400);
        }

        $operation = $this->operationRegistry->find($operationSlug);
        if ($operation === null) {
            return new JsonResponse(['success' => false, 'message' => 'Unknown operation!'], 404);
        }

        $requiredGameUnitId = $operation->getGameUnit();
        $maxDistance = $operation->getMaxDistance();

        $eligibleRegions = [];
        foreach ($player->getWorldRegions() as $playerRegion) {
            $distance = $this->distanceCalculator->calculateDistance(
                $playerRegion->getX(),
                $playerRegion->getY(),
                $worldRegion->getX(),
                $worldRegion->getY()
            );

            if ($distance > $maxDistance) {
                continue;
            }

            // Check if region has the required unit type (skip the check for unit-less ops like spy)
            if ($requiredGameUnitId !== null) {
                $hasUnit = false;
                foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                    if (
                        $worldRegionStackableUnit->getGameUnit() === $requiredGameUnitId
                        && $worldRegionStackableUnit->getAmount() > 0
                    ) {
                        $hasUnit = true;
                        break;
                    }
                }

                if (!$hasUnit) {
                    continue;
                }
            }

            // Skip regions with active bombardment cooldowns
            if ($operation->hasCooldown()) {
                $cooldown = $this->bombardmentCooldownRepository->findActiveByWorldRegionAndOperation(
                    $playerRegion,
                    $operation->getSlug()
                );
                if ($cooldown !== null) {
                    continue;
                }
            }

            $eligibleRegions[] = [
                'regionId' => $playerRegion->getId(),
                'x' => $playerRegion->getX(),
                'y' => $playerRegion->getY(),
                'distance' => $distance,
            ];
        }

        return new JsonResponse([
            'success' => true,
            'eligibleRegions' => $eligibleRegions,
        ]);
    }

    public function operationUnitsApi(int $regionId, string $operationSlug, int $playerRegionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        $operation = $this->operationRegistry->find($operationSlug);
        if ($operation === null) {
            return new JsonResponse(['success' => false, 'message' => 'Unknown operation!'], 404);
        }

        $requiredGameUnitId = $operation->getGameUnit();
        $availableAmount = 0;
        $unitName = null;
        $unitImage = null;

        if ($requiredGameUnitId !== null) {
            foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                if ($worldRegionStackableUnit->getGameUnit() === $requiredGameUnitId) {
                    $availableAmount = $worldRegionStackableUnit->getAmount();
                    break;
                }
            }

            $gameUnit = $this->gameUnitRegistry->find($requiredGameUnitId);
            $unitName = $gameUnit->getName();
            $unitImage = $gameUnit->getImage();
        }

        return new JsonResponse([
            'success' => true,
            'unitName' => $unitName,
            'unitImage' => $unitImage,
            'available' => $availableAmount,
            'costPerUnit' => $operation->getCost(),
            'totalCost' => $operation->calculateCost($worldRegion, 1),
            'playerCash' => $player->getResources()->getCash(),
        ]);
    }

    public function executeOperationApi(
        Request $request,
        int $regionId,
        string $operationSlug,
        int $playerRegionId
    ): JsonResponse {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        $operation = $this->operationRegistry->find($operationSlug);
        if ($operation === null) {
            return new JsonResponse(['success' => false, 'message' => 'Unknown operation!'], 404);
        }

        try {
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        /** @var array{amount?: int} $data */
        $data = json_decode($request->getContent(), true) ?? [];
        $amount = $data['amount'] ?? 0;

        try {
            $operationResults = $this->operationService->executeOperation(
                $worldRegion,
                $operation,
                $playerRegion,
                $amount
            );

            $response = [
                'success' => true,
                'message' => 'Operation executed!',
                'results' => $operationResults,
                'newCash' => $player->getResources()->getCash(),
            ];

            if ($operation->hasCooldown()) {
                $cooldown = $this->bombardmentCooldownRepository->findActiveByWorldRegionAndOperation(
                    $playerRegion,
                    $operation->getSlug()
                );
                if ($cooldown !== null) {
                    $response['bombardmentCooldown'] = [
                        'sourceX' => $playerRegion->getX(),
                        'sourceY' => $playerRegion->getY(),
                        'targetX' => $worldRegion->getX(),
                        'targetY' => $worldRegion->getY(),
                        'cooldownUntil' => $cooldown->getCooldownUntil(),
                        'remainingSeconds' => max(0, $cooldown->getCooldownUntil() - time()),
                    ];
                }
            }

            return new JsonResponse($response);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
