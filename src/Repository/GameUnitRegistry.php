<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\GameUnit\AntiAircraftGun;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Airfield;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Artillery;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Barrack;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Bomber;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Bunker;
use FrankProjects\UltimateWarfare\Entity\GameUnit\ChemicalRocket;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Cruiser;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Destroyer;
use FrankProjects\UltimateWarfare\Entity\GameUnit\EconomicCenter;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Factory;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Farm;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Fighter;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Harbor;
use FrankProjects\UltimateWarfare\Entity\GameUnit\House;
use FrankProjects\UltimateWarfare\Entity\GameUnit\MineField;
use FrankProjects\UltimateWarfare\Entity\GameUnit\IronMine;
use FrankProjects\UltimateWarfare\Entity\GameUnit\MineCountermeasuresShip;
use FrankProjects\UltimateWarfare\Entity\GameUnit\MineSweeper;
use FrankProjects\UltimateWarfare\Entity\GameUnit\MissileFactory;
use FrankProjects\UltimateWarfare\Entity\GameUnit\NuclearMissile;
use FrankProjects\UltimateWarfare\Entity\GameUnit\PatrolBoat;
use FrankProjects\UltimateWarfare\Entity\GameUnit\RadarStation;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Rocket;
use FrankProjects\UltimateWarfare\Entity\GameUnit\SeaMine;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Sniper;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Soldier;
use FrankProjects\UltimateWarfare\Entity\GameUnit\StrategicBomber;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Submarine;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Tank;
use FrankProjects\UltimateWarfare\Entity\GameUnit\TrainStation;
use FrankProjects\UltimateWarfare\Entity\GameUnit\Woodcutter;

final class GameUnitRegistry
{
    /** @var array<int, GameUnit> */
    private array $gameUnits;

    public function __construct()
    {
        $gameUnitList = [
            new EconomicCenter(),
            new Farm(),
            new IronMine(),
            new Woodcutter(),
            new House(),
            new SeaMine(),
            new MineField(),
            new Bunker(),
            new AntiAircraftGun(),
            new Airfield(),
            new Harbor(),
            new TrainStation(),
            new Barrack(),
            new Factory(),
            new RadarStation(),
            new MissileFactory(),
            new Soldier(),
            new Sniper(),
            new Tank(),
            new Artillery(),
            new MineSweeper(),
            new PatrolBoat(),
            new Destroyer(),
            new Cruiser(),
            new Submarine(),
            new MineCountermeasuresShip(),
            new Fighter(),
            new Bomber(),
            new StrategicBomber(),
            new Rocket(),
            new ChemicalRocket(),
            new NuclearMissile(),
        ];

        $this->gameUnits = [];
        foreach ($gameUnitList as $gameUnit) {
            $this->gameUnits[$gameUnit->getGameUnitEnum()->value] = $gameUnit;
        }
    }

    public function find(GameUnitEnum $gameUnitEnum): GameUnit
    {
        return $this->gameUnits[$gameUnitEnum->value]
            ?? throw new \RuntimeException("GameUnit not found for enum: {$gameUnitEnum->name}");
    }

    /**
     * @return GameUnit[]
     */
    public function findAll(): array
    {
        return array_values($this->gameUnits);
    }

    /**
     * @return GameUnit[]
     */
    public function findByCategory(GameUnitCategory $category): array
    {
        return array_values(
            array_filter(
                $this->gameUnits,
                static fn (GameUnit $gameUnit): bool => $gameUnit->getGameUnitCategory() === $category
            )
        );
    }

    /**
     * @param GameUnitCategory[] $categories
     * @return GameUnit[]
     */
    public function findByCategories(array $categories): array
    {
        return array_values(
            array_filter(
                $this->gameUnits,
                static fn (GameUnit $gameUnit): bool => in_array(
                    $gameUnit->getGameUnitCategory(),
                    $categories,
                    true
                )
            )
        );
    }

    /**
     * @return GameUnitEnum[]
     */
    public function getIdsByCategory(GameUnitCategory $category): array
    {
        return array_map(
            static fn (GameUnit $gameUnit): GameUnitEnum => $gameUnit->getGameUnitEnum(),
            $this->findByCategory($category)
        );
    }

    /**
     * Summary of a region's units per map category, including the construction queue, so it
     * must only be shown to the region owner. Per category it holds the owned count, the count
     * under construction (for leveled buildings: new buildings only, not upgrades) and a detail
     * row per unit type with its own construction count (for leveled buildings: queued levels).
     *
     * @return array<string, mixed>
     */
    public function getRegionUnitSummary(WorldRegion $region): array
    {
        $categoryKeys = ['buildings', 'defences', 'special', 'troops', 'navalUnits', 'airUnits', 'missiles'];

        $counts = array_fill_keys($categoryKeys, 0);
        $inConstruction = array_fill_keys($categoryKeys, 0);
        /** @var array<string, list<array{name: string, amount: int, inConstruction: int, level?: int}>> $details */
        $details = array_fill_keys($categoryKeys, []);
        // Detail row index per category and game unit, so constructions merge into existing rows
        /** @var array<string, array<int, int>> $rowIndex */
        $rowIndex = [];

        foreach ($region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            $gameUnit = $this->find($worldRegionStackableUnit->getGameUnit());
            $key = $this->getSummaryKey($gameUnit->getGameUnitCategory());
            $amount = $worldRegionStackableUnit->getAmount();

            $counts[$key] += $amount;
            $rowIndex[$key][$gameUnit->getGameUnitEnum()->value] = count($details[$key]);
            $details[$key][] = ['name' => $gameUnit->getName(), 'amount' => $amount, 'inConstruction' => 0];
        }

        // Leveled buildings (Defense / Special) are summarised by their level.
        foreach ($region->getWorldRegionLeveledUnits() as $leveledUnit) {
            $gameUnit = $this->find($leveledUnit->getGameUnit());
            if (!$gameUnit->getGameUnitCategory()->isLeveled()) {
                continue;
            }
            $key = $this->getSummaryKey($gameUnit->getGameUnitCategory());

            $counts[$key] += 1;
            $rowIndex[$key][$gameUnit->getGameUnitEnum()->value] = count($details[$key]);
            $details[$key][] = [
                'name' => $gameUnit->getName(),
                'amount' => 1,
                'inConstruction' => 0,
                'level' => $leveledUnit->getLevel(),
            ];
        }

        foreach ($region->getConstructions() as $construction) {
            $gameUnit = $this->find($construction->getGameUnit());
            $isLeveled = $gameUnit->getGameUnitCategory()->isLeveled();
            $key = $this->getSummaryKey($gameUnit->getGameUnitCategory());
            $gameUnitId = $gameUnit->getGameUnitEnum()->value;

            if (!isset($rowIndex[$key][$gameUnitId])) {
                $rowIndex[$key][$gameUnitId] = count($details[$key]);
                $details[$key][] = $isLeveled
                    ? ['name' => $gameUnit->getName(), 'amount' => 0, 'inConstruction' => 0, 'level' => 0]
                    : ['name' => $gameUnit->getName(), 'amount' => 0, 'inConstruction' => 0];

                if ($isLeveled) {
                    // A leveled building that does not exist yet
                    $inConstruction[$key] += 1;
                }
            }

            $details[$key][$rowIndex[$key][$gameUnitId]]['inConstruction'] += $construction->getNumber();
            if (!$isLeveled) {
                $inConstruction[$key] += $construction->getNumber();
            }
        }

        return array_merge($counts, ['inConstruction' => $inConstruction, 'details' => $details]);
    }

    /**
     * Get which unit categories are present in a region (without revealing counts).
     * Used for showing enemy unit indicators with hidden amounts.
     *
     * @return array<string, bool>
     */
    public function getRegionUnitCategoriesPresence(WorldRegion $region): array
    {
        $presence = [
            'buildings' => false,
            'defences' => false,
            'special' => false,
            'troops' => false,
            'navalUnits' => false,
            'airUnits' => false,
            'missiles' => false,
        ];

        foreach ($region->getWorldRegionStackableUnits() as $worldRegionStackableUnit) {
            if ($worldRegionStackableUnit->getAmount() > 0) {
                $gameUnit = $this->find($worldRegionStackableUnit->getGameUnit());
                $presence[$this->getSummaryKey($gameUnit->getGameUnitCategory())] = true;
            }
        }

        foreach ($region->getWorldRegionLeveledUnits() as $leveledUnit) {
            $gameUnit = $this->find($leveledUnit->getGameUnit());
            if ($gameUnit->getGameUnitCategory()->isLeveled()) {
                $presence[$this->getSummaryKey($gameUnit->getGameUnitCategory())] = true;
            }
        }

        return $presence;
    }

    private function getSummaryKey(GameUnitCategory $gameUnitCategory): string
    {
        return match ($gameUnitCategory) {
            GameUnitCategory::BUILDINGS => 'buildings',
            GameUnitCategory::DEFENSE_BUILDINGS => 'defences',
            GameUnitCategory::SPECIAL_BUILDINGS => 'special',
            GameUnitCategory::TROOPS => 'troops',
            GameUnitCategory::NAVAL_UNITS => 'navalUnits',
            GameUnitCategory::AIR_UNITS => 'airUnits',
            GameUnitCategory::MISSILES => 'missiles',
        };
    }
}
