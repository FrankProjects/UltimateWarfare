<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\FleetMovement;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Util\HexGrid;
use RuntimeException;

/**
 * Finds the region by region route of a fleet. Ground troops march through the player's own land regions, ships
 * sail through the player's own water regions and open sea, a land region is only entered or left over water.
 * Aircraft fly in a straight line.
 */
final class FleetRouteService
{
    /**
     * A fleet with ships sails, a fleet with only aircraft flies, any other fleet marches; aircraft travel along
     * with ships or ground troops
     *
     * @param list<GameUnit> $gameUnits
     */
    public function getMovement(array $gameUnits): FleetMovement
    {
        $hasNavalUnits = false;
        $hasGroundUnits = false;
        foreach ($gameUnits as $gameUnit) {
            $category = $gameUnit->getGameUnitCategory();
            if ($category === GameUnitCategory::NAVAL_UNITS) {
                $hasNavalUnits = true;
            } elseif ($category !== GameUnitCategory::AIR_UNITS) {
                $hasGroundUnits = true;
            }
        }

        if ($hasNavalUnits && $hasGroundUnits) {
            throw new RuntimeException("Ships and ground troops can't travel together, send them separately!");
        }

        return match (true) {
            $hasNavalUnits => FleetMovement::NAVAL,
            $hasGroundUnits => FleetMovement::GROUND,
            default => FleetMovement::AIR,
        };
    }

    /**
     * The route from the source to the target region, including both, or null when the target can't be reached
     *
     * @return non-empty-list<array{int, int}>|null
     */
    public function findRoute(
        WorldRegion $source,
        WorldRegion $target,
        Player $player,
        FleetMovement $movement
    ): ?array {
        if ($movement === FleetMovement::AIR) {
            return [[$source->getX(), $source->getY()], [$target->getX(), $target->getY()]];
        }

        // Ground troops never enter water
        if ($movement === FleetMovement::GROUND && $target->isWater()) {
            return null;
        }

        $paths = $this->searchPaths(
            $source,
            $player,
            $movement,
            fn (WorldRegion $region): bool => $region === $target
        );
        $path = $paths[$target->getX() . ',' . $target->getY()] ?? null;
        if ($path === null) {
            return null;
        }

        return array_map(static fn (WorldRegion $region): array => [$region->getX(), $region->getY()], $path);
    }

    /**
     * The player's regions from which a fleet reaches the target region, with the number of steps of the route
     *
     * @return array<int, int> Number of steps keyed by region id
     */
    public function findSourceRegions(WorldRegion $target, Player $player, FleetMovement $movement): array
    {
        if ($movement === FleetMovement::GROUND && $target->isWater()) {
            return [];
        }

        // Routes are reversible, so search backwards from the target
        return $this->searchRegions(
            $target,
            $player,
            $movement,
            fn (WorldRegion $region): bool => $this->isOwnedBy($region, $player)
                && ($movement === FleetMovement::NAVAL || !$region->isWater())
        );
    }

    /**
     * The regions a fleet can move to from the source region (the player's own regions and open sea), with the
     * number of steps of the route
     *
     * @return array<int, int> Number of steps keyed by region id
     */
    public function findTargetRegions(WorldRegion $source, Player $player, FleetMovement $movement): array
    {
        return $this->searchRegions(
            $source,
            $player,
            $movement,
            fn (WorldRegion $region): bool => $movement === FleetMovement::NAVAL
                ? $this->isOwnedBy($region, $player) || ($region->isWater() && $region->getPlayer() === null)
                : !$region->isWater() && $this->isOwnedBy($region, $player)
        );
    }

    /**
     * @param callable(WorldRegion): bool $isEndpoint Regions a route may start or end on
     * @return array<int, int> Number of steps keyed by region id, without the region searched from
     */
    private function searchRegions(
        WorldRegion $from,
        Player $player,
        FleetMovement $movement,
        callable $isEndpoint
    ): array {
        if ($movement === FleetMovement::AIR) {
            return [];
        }

        $steps = [];
        foreach ($this->searchPaths($from, $player, $movement, $isEndpoint) as $path) {
            $region = end($path);
            if ($region !== $from && $isEndpoint($region)) {
                $steps[$region->getId()] = count($path) - 1;
            }
        }

        return $steps;
    }

    /**
     * Routes from a region through the regions the movement can pass through, ending on any endpoint region
     *
     * @param callable(WorldRegion): bool $isEndpoint
     * @return array<string, non-empty-list<WorldRegion>> The route to every reached region, keyed by "x,y"
     */
    private function searchPaths(
        WorldRegion $from,
        Player $player,
        FleetMovement $movement,
        callable $isEndpoint
    ): array {
        $regionsByCoordinate = $this->getRegionsByCoordinate($player->getWorld());
        $paths = HexGrid::search(
            $from->getX(),
            $from->getY(),
            function (array $step, array $to) use ($regionsByCoordinate, $player, $movement, $isEndpoint): bool {
                $region = $regionsByCoordinate[$to[0] . ',' . $to[1]] ?? null;
                $stepRegion = $regionsByCoordinate[$step[0] . ',' . $step[1]];
                if ($region === null || !$this->canStep($stepRegion, $region, $movement)) {
                    return false;
                }

                return $isEndpoint($region) || $this->canPassThrough($region, $player, $movement);
            },
            fn (array $tile): bool => $this->canPassThrough(
                $regionsByCoordinate[$tile[0] . ',' . $tile[1]],
                $player,
                $movement
            )
        );

        return array_map(
            static fn (array $path): array => array_map(
                static fn (array $tile): WorldRegion => $regionsByCoordinate[$tile[0] . ',' . $tile[1]],
                $path
            ),
            $paths
        );
    }

    /**
     * Regions a route may lead through: own land for ground troops, own water or open sea for ships
     */
    private function canPassThrough(WorldRegion $region, Player $player, FleetMovement $movement): bool
    {
        if ($movement === FleetMovement::NAVAL) {
            return $region->isWater() && ($region->getPlayer() === null || $this->isOwnedBy($region, $player));
        }

        return !$region->isWater() && $this->isOwnedBy($region, $player);
    }

    /**
     * Ships only move between two regions when one of them is water
     */
    private function canStep(WorldRegion $from, WorldRegion $to, FleetMovement $movement): bool
    {
        return $movement !== FleetMovement::NAVAL || $from->isWater() || $to->isWater();
    }

    private function isOwnedBy(WorldRegion $region, Player $player): bool
    {
        return $region->getPlayer()?->getId() === $player->getId();
    }

    /**
     * @return array<string, WorldRegion>
     */
    private function getRegionsByCoordinate(World $world): array
    {
        $regionsByCoordinate = [];
        foreach ($world->getWorldRegions() as $worldRegion) {
            $regionsByCoordinate[$worldRegion->getX() . ',' . $worldRegion->getY()] = $worldRegion;
        }

        return $regionsByCoordinate;
    }
}
