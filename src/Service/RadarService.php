<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Util\HexGrid;

/**
 * A Radar Station sees every region within a radius equal to its level, and detects enemy fleets whose route
 * crosses that area
 */
final class RadarService
{
    private FleetRepository $fleetRepository;

    public function __construct(FleetRepository $fleetRepository)
    {
        $this->fleetRepository = $fleetRepository;
    }

    /**
     * @return array<string, true> Coordinates ("x,y") covered by the player's Radar Stations
     */
    public function getCoverage(Player $player): array
    {
        $coverage = [];
        foreach ($player->getWorldRegions() as $region) {
            $range = $region->getUnitLevel(GameUnitEnum::RADAR_STATION);
            if ($range === 0) {
                continue;
            }

            $x = $region->getX();
            $y = $region->getY();
            for ($ty = $y - $range; $ty <= $y + $range; $ty++) {
                for ($tx = $x - $range; $tx <= $x + $range; $tx++) {
                    if (HexGrid::distance($x, $y, $tx, $ty) <= $range) {
                        $coverage[$tx . ',' . $ty] = true;
                    }
                }
            }
        }

        return $coverage;
    }

    /**
     * Fleets of other players, except federation members, whose route crosses the player's radar coverage
     *
     * @param array<string, true> $coverage
     * @return list<Fleet>
     */
    public function findDetectedFleets(Player $player, array $coverage): array
    {
        if ($coverage === []) {
            return [];
        }

        $detectedFleets = [];
        foreach ($this->fleetRepository->findByWorldExcludingPlayer($player->getWorld(), $player) as $fleet) {
            if (!$this->isFederationMember($player, $fleet->getPlayer()) && $this->crossesCoverage($fleet, $coverage)) {
                $detectedFleets[] = $fleet;
            }
        }

        return $detectedFleets;
    }

    private function isFederationMember(Player $player, Player $other): bool
    {
        $federation = $player->getFederation();

        return $federation !== null && $other->getFederation()?->getId() === $federation->getId();
    }

    /**
     * Aircraft routes only hold their source and target, so every leg is followed tile by tile
     *
     * @param array<string, true> $coverage
     */
    private function crossesCoverage(Fleet $fleet, array $coverage): bool
    {
        $source = $fleet->getWorldRegion();
        $target = $fleet->getTargetWorldRegion();
        $path = $fleet->getPath();
        if (count($path) < 2) {
            $path = [[$source->getX(), $source->getY()], [$target->getX(), $target->getY()]];
        }

        for ($i = 1; $i < count($path); $i++) {
            foreach (HexGrid::line($path[$i - 1][0], $path[$i - 1][1], $path[$i][0], $path[$i][1]) as [$x, $y]) {
                if (isset($coverage[$x . ',' . $y])) {
                    return true;
                }
            }
        }

        return false;
    }
}
