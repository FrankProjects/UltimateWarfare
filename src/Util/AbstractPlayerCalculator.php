<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use RuntimeException;

/**
 * Unit totals are aggregated in the database (a fixed number of queries regardless of the amount of regions
 * and fleets), so unit changes must be flushed before calculating.
 *
 * @property AbstractGameResources $abstractGameResources
 */
abstract class AbstractPlayerCalculator
{
    protected const string ABSTRACT_GAME_RESOURCES_UPKEEP = 'upkeep';
    protected const string ABSTRACT_GAME_RESOURCES_INCOME = 'income';

    protected AbstractGameResources $abstractGameResources;

    public function __construct(
        private readonly GameUnitRegistry $gameUnitRegistry,
        private readonly WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        private readonly WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        private readonly FleetUnitRepository $fleetUnitRepository
    ) {
    }

    protected function calculateForFleets(Player $player, string $type): void
    {
        $this->calculateForGameUnitTotals($this->fleetUnitRepository->getAmountSumByPlayer($player), $type);
    }

    protected function calculateForWorldRegions(Player $player, string $type): void
    {
        $this->calculateForGameUnitTotals(
            $this->worldRegionStackableUnitRepository->getAmountSumByPlayer($player),
            $type
        );

        // Leveled buildings (Defense / Special) scale their income/upkeep by their level.
        $this->calculateForGameUnitTotals(
            $this->worldRegionLeveledUnitRepository->getLevelSumByPlayer($player),
            $type
        );
    }

    /**
     * @param array<int, int> $gameUnitTotals Amount (or level) per game unit id
     */
    private function calculateForGameUnitTotals(array $gameUnitTotals, string $type): void
    {
        foreach ($gameUnitTotals as $gameUnitId => $total) {
            $gameUnit = $this->gameUnitRegistry->find(GameUnitEnum::from($gameUnitId));
            $gameUnitResource = $this->getAbstractGameResources($gameUnit, $type);
            $this->updateAbstractGameResource($total, $gameUnitResource);
        }
    }

    private function updateAbstractGameResource(int $amount, AbstractGameResources $abstractGameResources): void
    {
        $this->abstractGameResources->addCash($amount * $abstractGameResources->getCash());
        $this->abstractGameResources->addFood($amount * $abstractGameResources->getFood());
        $this->abstractGameResources->addWood($amount * $abstractGameResources->getWood());
        $this->abstractGameResources->addSteel($amount * $abstractGameResources->getSteel());
    }

    private function getAbstractGameResources(GameUnit $gameUnit, string $type): AbstractGameResources
    {
        if ($type === AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_UPKEEP) {
            return $gameUnit->getUpkeep();
        } elseif ($type === AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME) {
            return $gameUnit->getIncome();
        }

        throw new RuntimeException("Invalid AbstractGameResource type {$type}");
    }
}
