<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\FleetMovement;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\FleetActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\FleetRouteService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class AttackController extends BaseGameController
{
    private FleetActionService $fleetActionService;
    private RegionActionService $regionActionService;
    private GameUnitRegistry $gameUnitRegistry;
    private FleetRouteService $fleetRouteService;

    public function __construct(
        FleetActionService $fleetActionService,
        RegionActionService $regionActionService,
        GameUnitRegistry $gameUnitRegistry,
        FleetRouteService $fleetRouteService
    ) {
        $this->fleetActionService = $fleetActionService;
        $this->regionActionService = $regionActionService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->fleetRouteService = $fleetRouteService;
    }

    public function attackFromRegionsApi(int $regionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $targetRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        if ($targetRegion->getPlayer() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot attack region without owner!'], 400);
        }

        if ($targetRegion->getPlayer()->getId() === $player->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot attack your own region!'], 400);
        }

        $attackRoutes = $this->findAttackRoutes($targetRegion, $player);

        $eligibleRegions = [];
        foreach ($player->getWorldRegions() as $playerRegion) {
            // The shortest distance over which one of the stationed units can attack
            $distance = null;
            foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
                if ($worldRegionStackableUnit->getAmount() <= 0) {
                    continue;
                }

                $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
                $unitDistance = $this->getAttackDistance($gameUnit, $playerRegion, $targetRegion, $attackRoutes);
                if ($unitDistance !== null && ($distance === null || $unitDistance < $distance)) {
                    $distance = $unitDistance;
                }
            }

            if ($distance !== null) {
                $eligibleRegions[] = [
                    'regionId' => $playerRegion->getId(),
                    'x' => $playerRegion->getX(),
                    'y' => $playerRegion->getY(),
                    'distance' => $distance,
                ];
            }
        }

        return new JsonResponse([
            'success' => true,
            'targetRegionId' => $regionId,
            'eligibleRegions' => $eligibleRegions,
        ]);
    }

    /**
     * API: Get the list of eligible game units from a player region to attack a target region.
     * Only returns units that satisfy range + terrain rules.
     */
    public function attackUnitsApi(int $regionId, int $playerRegionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $targetRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        if ($targetRegion->getPlayer() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot attack region without owner!'], 400);
        }

        if ($targetRegion->getPlayer()->getId() === $player->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot attack your own region!'], 400);
        }

        $attackRoutes = $this->findAttackRoutes($targetRegion, $player);

        $units = [];
        foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() <= 0) {
                continue;
            }

            $gameUnitEnum = $worldRegionStackableUnit->getGameUnit();
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            $category = $gameUnit->getGameUnitCategory();

            if ($this->getAttackDistance($gameUnit, $playerRegion, $targetRegion, $attackRoutes) === null) {
                continue;
            }

            // Only include combat unit categories
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
            'targetRegion' => [
                'id' => $targetRegion->getId(),
                'x' => $targetRegion->getX(),
                'y' => $targetRegion->getY(),
                'ownerName' => $targetRegion->getPlayer()->getName(),
            ],
        ]);
    }

    /**
     * API: Send attack fleet from player region to target region.
     */
    public function attackSendApi(Request $request, int $regionId, int $playerRegionId): JsonResponse
    {
        $player = $this->getPlayer();

        try {
            $targetRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        }

        if ($targetRegion->getPlayer() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot attack region without owner!'], 400);
        }

        if ($targetRegion->getPlayer()->getId() === $player->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cannot attack your own region!'], 400);
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($request->getContent(), true);
        /** @var array<string, int|string> $unitData */
        $unitData = is_array($data) && isset($data['units']) && is_array($data['units']) ? $data['units'] : [];

        if ($unitData === []) {
            return new JsonResponse(['success' => false, 'message' => 'No units selected!'], 400);
        }

        // Validate that all selected units can reach the target
        $attackRoutes = $this->findAttackRoutes($targetRegion, $player);

        // Build lookup of valid unit IDs for this attack
        $validUnitIds = [];
        foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() <= 0) {
                continue;
            }
            $gameUnitEnum = $worldRegionStackableUnit->getGameUnit();
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            if ($this->getAttackDistance($gameUnit, $playerRegion, $targetRegion, $attackRoutes) !== null) {
                $validUnitIds[$gameUnitEnum->value] = true;
            }
        }

        // Filter out any units the player tried to send that aren't valid
        $filteredUnits = [];
        foreach ($unitData as $gameUnitId => $amount) {
            if (!is_numeric($amount)) {
                continue;
            }
            $amountInt = (int) $amount;
            if ($amountInt > 0 && isset($validUnitIds[(int) $gameUnitId])) {
                $filteredUnits[(int) $gameUnitId] = (string) $amountInt;
            }
        }

        if ($filteredUnits === []) {
            return new JsonResponse(
                ['success' => false, 'message' => 'None of the selected units can reach the target!'],
                400
            );
        }

        try {
            $fleet = $this->fleetActionService->sendGameUnits(
                $playerRegion,
                $targetRegion,
                $player,
                $filteredUnits
            );
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }

        // Build unit list for frontend
        $sentUnits = [];
        $totalUnitCount = 0;
        foreach ($filteredUnits as $gameUnitId => $amount) {
            $gameUnitEnum = GameUnitEnum::from($gameUnitId);
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            $sentUnits[] = [
                'name' => $gameUnit->getName(),
                'amount' => (int) $amount,
            ];
            $totalUnitCount += (int) $amount;
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Attack fleet dispatched!',
            'fleet' => [
                'id' => $fleet->getId(),
                'sourceX' => $playerRegion->getX(),
                'sourceY' => $playerRegion->getY(),
                'targetX' => $targetRegion->getX(),
                'targetY' => $targetRegion->getY(),
                'path' => $fleet->getPath(),
                'targetRegionId' => $targetRegion->getId(),
                'targetIsYours' => false,
                'timestamp' => $fleet->getTimestamp(),
                'timestampArrive' => $fleet->getTimestampArrive(),
                'hasArrived' => false,
                'eta' => max(0, $fleet->getTimestampArrive() - time()),
                'unitCount' => $totalUnitCount,
                'units' => $sentUnits,
            ],
            'sourceRegionId' => $playerRegion->getId(),
            'sourceRegionUnits' => $this->gameUnitRegistry->getRegionUnitSummary($playerRegion),
            // A water region is released when its last units leave
            'sourceRegionReleased' => $playerRegion->getPlayer() === null,
        ]);
    }

    /**
     * The player's regions from which ground troops and ships reach the target, with the length of their route.
     * Ground troops march through your own regions and attack a neighboring region, ships sail over water to a
     * water or coastal region.
     *
     * @return array{ground: array<int, int>, naval: array<int, int>} Route steps keyed by region id
     */
    private function findAttackRoutes(WorldRegion $targetRegion, Player $player): array
    {
        return [
            'ground' => $this->fleetRouteService->findSourceRegions($targetRegion, $player, FleetMovement::GROUND),
            'naval' => $this->fleetRouteService->findSourceRegions($targetRegion, $player, FleetMovement::NAVAL),
        ];
    }

    /**
     * The distance over which a unit attacks the target from the player region, or null when it can't attack it
     *
     * Rules:
     * - Troops: march through your own regions to a region next to the target
     * - Naval units: sail through your own water and open sea, from and to water or coastal regions
     * - Aircraft fly in a straight line: Fighter range 3, Bomber range 5, Strategic Bomber range 7
     *
     * @param array{ground: array<int, int>, naval: array<int, int>} $attackRoutes
     */
    private function getAttackDistance(
        GameUnit $gameUnit,
        WorldRegion $playerRegion,
        WorldRegion $targetRegion,
        array $attackRoutes
    ): ?int {
        return match ($gameUnit->getGameUnitCategory()) {
            GameUnitCategory::TROOPS => $attackRoutes['ground'][$playerRegion->getId()] ?? null,
            GameUnitCategory::NAVAL_UNITS => $attackRoutes['naval'][$playerRegion->getId()] ?? null,
            GameUnitCategory::AIR_UNITS => $this->getAirAttackDistance($gameUnit, $playerRegion, $targetRegion),
            default => null,
        };
    }

    private function getAirAttackDistance(
        GameUnit $gameUnit,
        WorldRegion $playerRegion,
        WorldRegion $targetRegion
    ): ?int {
        $range = match ($gameUnit->getRowName()) {
            'fighter' => 3,
            'bomber' => 5,
            'strategic_bomber' => 7,
            default => 0,
        };

        $distance = $this->calculateTileDistance(
            $playerRegion->getX(),
            $playerRegion->getY(),
            $targetRegion->getX(),
            $targetRegion->getY()
        );

        return $distance <= $range ? $distance : null;
    }

    /**
     * Calculate Chebyshev distance (tiles away) between two coordinates.
     * This counts diagonal movement as 1 tile.
     */
    private function calculateTileDistance(int $x1, int $y1, int $x2, int $y2): int
    {
        return max(abs($x1 - $x2), abs($y1 - $y2));
    }
}
