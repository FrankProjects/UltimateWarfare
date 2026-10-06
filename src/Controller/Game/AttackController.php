<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\FleetActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class AttackController extends BaseGameController
{
    private FleetActionService $fleetActionService;
    private RegionActionService $regionActionService;
    private GameUnitRegistry $gameUnitRegistry;
    private DistanceCalculator $distanceCalculator;

    public function __construct(
        FleetActionService $fleetActionService,
        RegionActionService $regionActionService,
        GameUnitRegistry $gameUnitRegistry,
        DistanceCalculator $distanceCalculator
    ) {
        $this->fleetActionService = $fleetActionService;
        $this->regionActionService = $regionActionService;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->distanceCalculator = $distanceCalculator;
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

        // Build a lookup of all world regions by coordinate for adjacency checks
        $world = $player->getWorld();
        $regionsByCoord = [];
        foreach ($world->getWorldRegions() as $wr) {
            $regionsByCoord[$wr->getX() . ',' . $wr->getY()] = $wr;
        }

        $targetX = $targetRegion->getX();
        $targetY = $targetRegion->getY();

        // Check if target region is water/sand or adjacent to water/sand (for naval rule)
        $targetIsCoastal = $this->isCoastalOrWater($targetRegion, $regionsByCoord);

        $eligibleRegions = [];

        foreach ($player->getWorldRegions() as $playerRegion) {
            $distance = $this->calculateTileDistance(
                $playerRegion->getX(),
                $playerRegion->getY(),
                $targetX,
                $targetY
            );

            // Determine max attack range from this region based on stationed units
            $maxRange = $this->getMaxAttackRange($playerRegion, $targetIsCoastal, $regionsByCoord);

            if ($maxRange > 0 && $distance <= $maxRange) {
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

        $distance = $this->calculateTileDistance(
            $playerRegion->getX(),
            $playerRegion->getY(),
            $targetRegion->getX(),
            $targetRegion->getY()
        );

        $regionsByCoord = [];
        foreach ($player->getWorld()->getWorldRegions() as $wr) {
            $regionsByCoord[$wr->getX() . ',' . $wr->getY()] = $wr;
        }

        $targetIsCoastal = $this->isCoastalOrWater($targetRegion, $regionsByCoord);
        $sourceIsCoastal = $this->isCoastalOrWater($playerRegion, $regionsByCoord);

        $units = [];
        foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() <= 0) {
                continue;
            }

            $gameUnitEnum = $worldRegionStackableUnit->getGameUnit();
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            $category = $gameUnit->getGameUnitCategory();
            $rowName = $gameUnit->getRowName();

            $unitRange = $this->getUnitRange($category, $rowName, $sourceIsCoastal, $targetIsCoastal);

            if ($unitRange <= 0 || $distance > $unitRange) {
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

        // Validate that all selected units are within range
        $distance = $this->calculateTileDistance(
            $playerRegion->getX(),
            $playerRegion->getY(),
            $targetRegion->getX(),
            $targetRegion->getY()
        );

        $regionsByCoord = [];
        foreach ($player->getWorld()->getWorldRegions() as $wr) {
            $regionsByCoord[$wr->getX() . ',' . $wr->getY()] = $wr;
        }

        $targetIsCoastal = $this->isCoastalOrWater($targetRegion, $regionsByCoord);
        $sourceIsCoastal = $this->isCoastalOrWater($playerRegion, $regionsByCoord);

        // Build lookup of valid unit IDs for this attack
        $validUnitIds = [];
        foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() <= 0) {
                continue;
            }
            $gameUnitEnum = $worldRegionStackableUnit->getGameUnit();
            $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
            $unitRange = $this->getUnitRange(
                $gameUnit->getGameUnitCategory(),
                $gameUnit->getRowName(),
                $sourceIsCoastal,
                $targetIsCoastal
            );
            if ($unitRange > 0 && $distance <= $unitRange) {
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

        // Calculate travel time for the ETA
        $travelTime = $this->distanceCalculator->calculateFleetTravelTime(
            $targetRegion->getX(),
            $targetRegion->getY(),
            $playerRegion->getX(),
            $playerRegion->getY(),
            $playerRegion->getUnitLevel(GameUnitEnum::TRAIN_STATION)
        );

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
                'targetRegionId' => $targetRegion->getId(),
                'targetIsYours' => false,
                'timestampArrive' => time() + $travelTime,
                'hasArrived' => false,
                'eta' => $travelTime,
                'unitCount' => $totalUnitCount,
                'units' => $sentUnits,
            ],
            'sourceRegionId' => $playerRegion->getId(),
            'sourceRegionUnits' => $this->gameUnitRegistry->getRegionUnitSummary($playerRegion),
        ]);
    }

    /**
     * Get the attack range for a specific unit type.
     */
    private function getUnitRange(
        GameUnitCategory $category,
        string $rowName,
        bool $sourceIsCoastal,
        bool $targetIsCoastal
    ): int {
        return match (true) {
            $category === GameUnitCategory::TROOPS
                && in_array($rowName, ['soldier', 'sniper', 'minesweeper'], true) => 1,
            $category === GameUnitCategory::TROOPS && in_array($rowName, ['tank', 'artillery'], true) => 2,
            $category === GameUnitCategory::AIR_UNITS && $rowName === 'fighter' => 3,
            $category === GameUnitCategory::AIR_UNITS && $rowName === 'bomber' => 5,
            $category === GameUnitCategory::AIR_UNITS && $rowName === 'strategic_bomber' => 7,
            $category === GameUnitCategory::NAVAL_UNITS => ($sourceIsCoastal && $targetIsCoastal) ? 1 : 0,
            default => 0,
        };
    }

    /**
     * Calculate Chebyshev distance (tiles away) between two coordinates.
     * This counts diagonal movement as 1 tile.
     */
    private function calculateTileDistance(int $x1, int $y1, int $x2, int $y2): int
    {
        return max(abs($x1 - $x2), abs($y1 - $y2));
    }

    /**
     * Check if a region is water/sand or directly adjacent to a water/sand region.
     *
     * @param WorldRegion $region
     * @param array<string, WorldRegion> $regionsByCoord
     */
    private function isCoastalOrWater(WorldRegion $region, array $regionsByCoord): bool
    {
        $waterTypes = [
            WorldRegion::TYPE_DEEP_WATER, WorldRegion::TYPE_WATER,
            WorldRegion::TYPE_SHALLOW_WATER, WorldRegion::TYPE_SAND,
        ];

        if (in_array($region->getType(), $waterTypes, true)) {
            return true;
        }

        // Check 4 adjacent tiles
        $x = $region->getX();
        $y = $region->getY();
        $adjacentOffsets = [[-1, 0], [1, 0], [0, -1], [0, 1]];

        foreach ($adjacentOffsets as [$dx, $dy]) {
            $key = ($x + $dx) . ',' . ($y + $dy);
            if (isset($regionsByCoord[$key])) {
                if (in_array($regionsByCoord[$key]->getType(), $waterTypes, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine the maximum attack range from a player region based on stationed units.
     *
     * Rules:
     * - Infantry (Soldier, Sniper, Mine Sweeper): range 1
     * - Tank, Artillery: range 2
     * - Fighter: range 3
     * - Bomber: range 5
     * - Strategic Bomber: range 7
     * - Naval units: can only attack if target is coastal/water
     *
     * @param WorldRegion $playerRegion
     * @param bool $targetIsCoastal
     * @param array<string, WorldRegion> $regionsByCoord
     */
    private function getMaxAttackRange(WorldRegion $playerRegion, bool $targetIsCoastal, array $regionsByCoord): int
    {
        $maxRange = 0;
        $sourceIsCoastal = $this->isCoastalOrWater($playerRegion, $regionsByCoord);

        foreach ($playerRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() <= 0) {
                continue;
            }

            $gameUnit = $this->gameUnitRegistry->find($worldRegionStackableUnit->getGameUnit());
            $unitRange = $this->getUnitRange(
                $gameUnit->getGameUnitCategory(),
                $gameUnit->getRowName(),
                $sourceIsCoastal,
                $targetIsCoastal
            );

            $maxRange = max($maxRange, $unitRange);
        }

        return $maxRange;
    }
}
