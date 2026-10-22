<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;

/**
 * Regions of the defender next to an attacked region send part of their garrison through their
 * Train Station (troops), Airfield (aircraft) and Harbor (ships) to help defend it.
 */
final class BattleReinforcementService
{
    private const int TRAIN_STATION_SOLDIERS_PER_LEVEL = 25;
    private const int TRAIN_STATION_TANKS_PER_LEVEL = 1;
    private const int AIRFIELD_AIRCRAFT_PER_LEVEL = 10;
    private const int HARBOR_SHIPS_PER_LEVEL = 1;

    private const array AIRCRAFT_PRIORITY = [
        GameUnitEnum::FIGHTER,
        GameUnitEnum::BOMBER,
        GameUnitEnum::STRATEGIC_BOMBER,
    ];

    private const array SHIP_PRIORITY = [
        GameUnitEnum::CRUISER,
        GameUnitEnum::DESTROYER,
        GameUnitEnum::SUBMARINE,
        GameUnitEnum::PATROL_BOAT,
        GameUnitEnum::MINE_COUNTERMEASURES_SHIP,
    ];

    private WorldRegionRepository $worldRegionRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    /**
     * @return Reinforcement[]
     */
    public function gatherReinforcements(WorldRegion $targetWorldRegion, Player $defender): array
    {
        $adjacentRegions = $this->worldRegionRepository->findAdjacentRegions(
            $targetWorldRegion->getX(),
            $targetWorldRegion->getY(),
            $targetWorldRegion->getWorld()
        );

        $reinforcements = [];
        foreach ($adjacentRegions as $worldRegion) {
            if ($worldRegion->getPlayer() !== $defender) {
                continue;
            }

            $trainStationLevel = $this->getBuildingLevel($worldRegion, GameUnitEnum::TRAIN_STATION);
            $airfieldLevel = $this->getBuildingLevel($worldRegion, GameUnitEnum::AIRFIELD);
            $harborLevel = $this->getBuildingLevel($worldRegion, GameUnitEnum::HARBOR);

            $reinforcements = [
                ...$reinforcements,
                ...$this->send(
                    $worldRegion,
                    GameUnitEnum::TRAIN_STATION,
                    [GameUnitEnum::SOLDIER],
                    $trainStationLevel * self::TRAIN_STATION_SOLDIERS_PER_LEVEL
                ),
                ...$this->send(
                    $worldRegion,
                    GameUnitEnum::TRAIN_STATION,
                    [GameUnitEnum::TANK],
                    $trainStationLevel * self::TRAIN_STATION_TANKS_PER_LEVEL
                ),
                ...$this->send(
                    $worldRegion,
                    GameUnitEnum::AIRFIELD,
                    self::AIRCRAFT_PRIORITY,
                    $airfieldLevel * self::AIRFIELD_AIRCRAFT_PER_LEVEL
                ),
                ...$this->send(
                    $worldRegion,
                    GameUnitEnum::HARBOR,
                    self::SHIP_PRIORITY,
                    $harborLevel * self::HARBOR_SHIPS_PER_LEVEL
                ),
            ];
        }

        return $reinforcements;
    }

    /**
     * One log line per building per region, e.g.
     * "Reinforcements arrive by train from region 12, 7: 25 Soldiers, 1 Tank"
     *
     * @param Reinforcement[] $reinforcements
     *
     * @return array<string>
     */
    public function getBattleLog(array $reinforcements): array
    {
        $shipments = [];
        foreach ($reinforcements as $reinforcement) {
            $worldRegion = $reinforcement->getSourceUnit()->getWorldRegion();
            $key = spl_object_id($worldRegion) . '-' . $reinforcement->getBuilding()->value;
            $shipments[$key]['region'] = $worldRegion->getRegionName();
            $shipments[$key]['building'] = $reinforcement->getBuilding();
            $shipments[$key]['units'][] = $this->getUnitsName(
                $reinforcement->getBattleUnit()->getGameUnit(),
                $reinforcement->getAmount()
            );
        }

        $battleLog = [];
        foreach ($shipments as $shipment) {
            $transport = match ($shipment['building']) {
                GameUnitEnum::TRAIN_STATION => 'arrive by train',
                GameUnitEnum::AIRFIELD => 'fly in',
                default => 'sail in',
            };
            $battleLog[] = "Reinforcements {$transport} from region {$shipment['region']}: "
                . implode(', ', $shipment['units']);
        }

        return $battleLog;
    }

    /**
     * Reinforcements that died are taken from their home region; survivors stay there
     *
     * @param Reinforcement[] $reinforcements
     * @param array<WorldRegionStackableUnit|WorldRegionLeveledUnit> $survivingDefenderGameUnits
     */
    public function applyLosses(array $reinforcements, array $survivingDefenderGameUnits): void
    {
        foreach ($reinforcements as $reinforcement) {
            $battleUnit = $reinforcement->getBattleUnit();
            $surviving = in_array($battleUnit, $survivingDefenderGameUnits, true) ? $battleUnit->getAmount() : 0;
            $lost = $reinforcement->getAmount() - $surviving;
            if ($lost <= 0) {
                continue;
            }

            $sourceUnit = $reinforcement->getSourceUnit();
            $remainingAmount = $sourceUnit->getAmount() - $lost;
            if ($remainingAmount <= 0) {
                $sourceUnit->getWorldRegion()->removeWorldRegionStackableUnit($sourceUnit);
                $this->worldRegionStackableUnitRepository->remove($sourceUnit);
                continue;
            }

            $sourceUnit->setAmount($remainingAmount);
            $this->worldRegionStackableUnitRepository->save($sourceUnit);
        }
    }

    /**
     * @param GameUnitEnum[] $priority
     *
     * @return Reinforcement[]
     */
    private function send(WorldRegion $worldRegion, GameUnitEnum $building, array $priority, int $capacity): array
    {
        $reinforcements = [];
        foreach ($priority as $gameUnitEnum) {
            if ($capacity <= 0) {
                break;
            }

            $sourceUnit = $this->findStackableUnit($worldRegion, $gameUnitEnum);
            if ($sourceUnit === null || $sourceUnit->getAmount() <= 0) {
                continue;
            }

            $amount = min($capacity, $sourceUnit->getAmount());
            $capacity -= $amount;

            $reinforcements[] = new Reinforcement($sourceUnit, $building, $amount);
        }

        return $reinforcements;
    }

    private function getBuildingLevel(WorldRegion $worldRegion, GameUnitEnum $building): int
    {
        $leveledUnit = $worldRegion->getLeveledUnit($building);
        if ($leveledUnit === null || $leveledUnit->getHealth() <= 0) {
            return 0;
        }

        return $leveledUnit->getLevel();
    }

    private function findStackableUnit(WorldRegion $worldRegion, GameUnitEnum $gameUnitEnum): ?WorldRegionStackableUnit
    {
        foreach ($worldRegion->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getGameUnit() === $gameUnitEnum) {
                return $worldRegionStackableUnit;
            }
        }

        return null;
    }

    private function getUnitsName(GameUnitEnum $gameUnitEnum, int $amount): string
    {
        $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);

        return $amount === 1 ? "1 {$gameUnit->getName()}" : "{$amount} {$gameUnit->getNameMulti()}";
    }
}
