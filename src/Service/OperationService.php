<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\BombardmentCooldown;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\BombardmentCooldownRepository;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;
use FrankProjects\UltimateWarfare\Util\ReportCreator;
use RuntimeException;

final class OperationService
{
    private const int BOMBARDMENT_COOLDOWN_SECONDS = 600;

    private ReportCreator $reportCreator;
    private NetWorthUpdaterService $netWorthUpdaterService;
    private IncomeUpdaterService $incomeUpdaterService;
    private PlayerRepository $playerRepository;
    private WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    private WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;
    private WorldRegionRepository $worldRegionRepository;
    private ConstructionRepository $constructionRepository;
    private BombardmentCooldownRepository $bombardmentCooldownRepository;
    private GameUnitRegistry $gameUnitRegistry;

    public function __construct(
        ReportCreator $reportCreator,
        NetWorthUpdaterService $netWorthUpdaterService,
        IncomeUpdaterService $incomeUpdaterService,
        PlayerRepository $playerRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository,
        BombardmentCooldownRepository $bombardmentCooldownRepository,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->reportCreator = $reportCreator;
        $this->netWorthUpdaterService = $netWorthUpdaterService;
        $this->incomeUpdaterService = $incomeUpdaterService;
        $this->playerRepository = $playerRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->constructionRepository = $constructionRepository;
        $this->bombardmentCooldownRepository = $bombardmentCooldownRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function executeOperation(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount
    ): array {
        $this->ensureCanExecute($region, $operation, $playerRegion, $amount);
        $this->hasWorldRegionGameUnitAmount($playerRegion, $operation, $amount);
        $this->ensureNoBombardmentCooldown($playerRegion, $operation);

        $player = $playerRegion->getPlayer();
        if ($player === null) {
            throw new RuntimeException("Region has no owner");
        }

        $player->getResources()->addCash(-$operation->calculateCost($region, $amount));
        $this->playerRepository->save($player);

        $operationProcessor = OperationProcessor::factory(
            $region,
            $operation,
            $playerRegion,
            $amount,
            $this->reportCreator,
            $this->playerRepository,
            $this->worldRegionStackableUnitRepository,
            $this->worldRegionLeveledUnitRepository,
            $this->worldRegionRepository,
            $this->constructionRepository,
            $this->gameUnitRegistry
        );
        $operationResults = $operationProcessor->execute();

        $this->createBombardmentCooldown($region, $operation, $playerRegion);

        $regionPlayer = $region->getPlayer();
        if ($regionPlayer !== null) {
            $this->netWorthUpdaterService->updateNetWorthForPlayer($regionPlayer);
            $this->incomeUpdaterService->updateIncomeForPlayer($regionPlayer);
        }

        $this->netWorthUpdaterService->updateNetWorthForPlayer($player);
        $this->incomeUpdaterService->updateIncomeForPlayer($player);

        return $operationResults;
    }

    private function ensureCanExecute(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount
    ): void {
        if (!$operation->isEnabled()) {
            throw new RuntimeException("Operation not enabled");
        }

        if ($region->getWorld()->getId() !== $playerRegion->getWorld()->getId()) {
            throw new RuntimeException("Regions not in same world");
        }

        if ($region->getPlayer() === null) {
            throw new RuntimeException("Target region has no owner");
        }

        if ($playerRegion->getPlayer() === null) {
            throw new RuntimeException("Region has no owner");
        }

        if ($region->getPlayer()->getId() === $playerRegion->getPlayer()->getId()) {
            throw new RuntimeException("You can not attack yourself");
        }

        if ($playerRegion->getPlayer()->getResources()->getCash() < $operation->calculateCost($region, $amount)) {
            throw new RuntimeException("You do not have enough cash");
        }

        foreach ($playerRegion->getPlayer()->getPlayerResearch() as $playerResearch) {
            if (
                $playerResearch->getResearchSlug() === $operation->getResearchSlug() &&
                $playerResearch->getActive() === true &&
                $playerResearch->getLevel() >= $operation->getResearchMinLevel()
            ) {
                return;
            }
        }
        throw new RuntimeException("You do not have all requirements to perform this operation");
    }

    private function hasWorldRegionGameUnitAmount(WorldRegion $region, Operation $operation, int $amount): void
    {
        // Unit-less operations (e.g., spy ops) skip the unit-count check.
        if ($operation->getGameUnit() === null) {
            return;
        }

        if ($amount < 1) {
            throw new RuntimeException("Can not send negative game units");
        }

        foreach ($region->getWorldRegionStackableUnits() as $regionUnit) {
            if ($regionUnit->getGameUnit() === $operation->getGameUnit()) {
                if ($regionUnit->getAmount() >= $amount) {
                    return;
                }
            }
        }
        throw new RuntimeException("Not enough game units");
    }

    private function ensureNoBombardmentCooldown(WorldRegion $playerRegion, Operation $operation): void
    {
        $cooldown = $this->bombardmentCooldownRepository->findActiveByWorldRegionAndOperation(
            $playerRegion,
            $operation->getSlug()
        );

        if ($cooldown !== null) {
            $remaining = $cooldown->getCooldownUntil() - time();
            $minutes = intval($remaining / 60);
            $seconds = $remaining % 60;
            throw new RuntimeException("Units are recharging. Ready in {$minutes}m {$seconds}s");
        }
    }

    private function createBombardmentCooldown(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion
    ): void {
        if (!$operation->hasCooldown()) {
            return;
        }

        $cooldown = BombardmentCooldown::create(
            $operation->getSlug(),
            $playerRegion,
            $region,
            time() + self::BOMBARDMENT_COOLDOWN_SECONDS
        );

        $this->bombardmentCooldownRepository->save($cooldown);
    }
}
