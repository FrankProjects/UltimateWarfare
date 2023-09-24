<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor;
use FrankProjects\UltimateWarfare\Util\ReportCreator;
use RuntimeException;

final class OperationService
{
    private ReportCreator $reportCreator;
    private NetworthUpdaterService $networthUpdaterService;
    private IncomeUpdaterService $incomeUpdaterService;
    private PlayerRepository $playerRepository;
    private WorldRegionUnitRepository $worldRegionUnitRepository;
    private WorldRegionRepository $worldRegionRepository;
    private ConstructionRepository $constructionRepository;

    public function __construct(
        ReportCreator $reportCreator,
        NetworthUpdaterService $networthUpdaterService,
        IncomeUpdaterService $incomeUpdaterService,
        PlayerRepository $playerRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository
    ) {
        $this->reportCreator = $reportCreator;
        $this->networthUpdaterService = $networthUpdaterService;
        $this->incomeUpdaterService = $incomeUpdaterService;
        $this->playerRepository = $playerRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->constructionRepository = $constructionRepository;
    }

    /**
     * @return array<int, string>
     */
    public function executeOperation(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount
    ): array {
        $this->ensureCanExecute($region, $operation, $playerRegion, $amount);
        $this->hasWorldRegionGameUnitAmount($playerRegion, $operation, $amount);

        $player = $playerRegion->getPlayer();
        $player->getResources()->addCash(-($operation->getCost() * $amount));
        $this->playerRepository->save($player);

        $operationProcessor = OperationProcessor::factory(
            $operation->getSubclass(),
            $region,
            $operation,
            $playerRegion,
            $amount,
            $this->reportCreator,
            $this->playerRepository,
            $this->worldRegionUnitRepository,
            $this->worldRegionRepository,
            $this->constructionRepository
        );
        $operationResults = $operationProcessor->execute();

        $this->networthUpdaterService->updateNetworthForPlayer($region->getPlayer());
        $this->networthUpdaterService->updateNetworthForPlayer($playerRegion->getPlayer());

        $this->incomeUpdaterService->updateIncomeForPlayer($region->getPlayer());
        $this->incomeUpdaterService->updateIncomeForPlayer($playerRegion->getPlayer());

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

        if ($region->getPlayer()->getId() === $playerRegion->getPlayer()->getId()) {
            throw new RuntimeException("You can not attack yourself");
        }

        if ($playerRegion->getPlayer()->getResources()->getCash() < $operation->getCost() * $amount) {
            throw new RuntimeException("You do not have enough cash");
        }

        foreach ($playerRegion->getPlayer()->getPlayerResearch() as $playerResearch) {
            if (
                $playerResearch->getResearch()->getId() === $operation->getResearch()->getId() &&
                $playerResearch->getResearch()->getActive() === true
            ) {
                return;
            }
        }
        throw new RuntimeException("You do not have all requirements to perform this operation");
    }

    private function hasWorldRegionGameUnitAmount(WorldRegion $region, Operation $operation, int $amount): void
    {
        if ($amount < 1) {
            throw new RuntimeException("Can not send negative game units");
        }

        foreach ($region->getWorldRegionUnits() as $regionUnit) {
            if ($regionUnit->getGameUnit()->getId() === $operation->getGameUnit()->getId()) {
                if ($regionUnit->getAmount() >= $amount) {
                    return;
                }
            }
        }
        throw new RuntimeException("Not enough game units");
    }
}
