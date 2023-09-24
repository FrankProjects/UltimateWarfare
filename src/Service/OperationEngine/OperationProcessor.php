<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine;

use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use FrankProjects\UltimateWarfare\Util\ReportCreator;
use RuntimeException;

abstract class OperationProcessor implements OperationInterface
{
    protected const GAME_UNIT_SPECIAL_OPS_ID = 401;
    protected const GAME_UNIT_GUARD_ID = 400;
    protected WorldRegion $region;
    protected Operation $operation;
    protected WorldRegion $playerRegion;
    protected int $amount;
    protected ReportCreator $reportCreator;
    protected PlayerRepository $playerRepository;
    protected WorldRegionUnitRepository $worldRegionUnitRepository;
    protected WorldRegionRepository $worldRegionRepository;
    protected ConstructionRepository $constructionRepository;
    /**
     * @var array <int, string>
     */
    protected array $operationLog = [];

    /**
     * OperationProcessor constructor.
     *
     * @param WorldRegion $region
     * @param Operation $operation
     * @param WorldRegion $playerRegion
     * @param int $amount
     * @param ReportCreator $reportCreator
     * @param PlayerRepository $playerRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param ConstructionRepository $constructionRepository
     */
    private function __construct(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount,
        ReportCreator $reportCreator,
        PlayerRepository $playerRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository
    ) {
        $this->region = $region;
        $this->operation = $operation;
        $this->playerRegion = $playerRegion;
        $this->amount = $amount;
        $this->reportCreator = $reportCreator;
        $this->playerRepository = $playerRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->constructionRepository = $constructionRepository;
    }

    /**
     * @param string $subclass
     * @param WorldRegion $region
     * @param Operation $operation
     * @param WorldRegion $playerRegion
     * @param int $amount
     * @param ReportCreator $reportCreator
     * @param PlayerRepository $playerRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param ConstructionRepository $constructionRepository
     * @return OperationInterface
     */
    public static function factory(
        string $subclass,
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount,
        ReportCreator $reportCreator,
        PlayerRepository $playerRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository
    ): OperationInterface {
        $className = "FrankProjects\\UltimateWarfare\\Service\\OperationEngine\\OperationProcessor\\" . $subclass;
        if (!class_exists($className) || is_subclass_of($className, OperationInterface::class) === false) {
            throw new RuntimeException("Unknown Operation {$subclass}");
        }

        return new $className(
            $region,
            $operation,
            $playerRegion,
            $amount,
            $reportCreator,
            $playerRepository,
            $worldRegionUnitRepository,
            $worldRegionRepository,
            $constructionRepository
        );
    }

    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        $this->addToOperationLog("Launching operation {$this->operation->getName()}!");
        $this->processPreOperation();
        $formula = $this->getFormula();

        if ($formula <= 0) {
            $this->processFailed();
        } else {
            $this->processSuccess();
        }

        $this->processPostOperation();

        return $this->getOperationLog();
    }

    protected function getRandomChance(): float
    {
        $random = mt_rand(0, 2);
        return ($random - 1) / 10;
    }

    protected function getSpecialOps(): int
    {
        foreach ($this->playerRegion->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_SPECIAL_OPS_ID) {
                return $worldRegionUnit->getAmount();
            }
        }

        return 0;
    }

    protected function getGuards(): int
    {
        foreach ($this->region->getWorldRegionUnits() as $worldRegionUnit) {
            if ($worldRegionUnit->getGameUnit()->getId() === self::GAME_UNIT_GUARD_ID) {
                return $worldRegionUnit->getAmount();
            }
        }

        return 0;
    }

    protected function hasResearched(int $researchId): bool
    {
        foreach ($this->playerRegion->getPlayer()->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() === false) {
                continue;
            }

            if ($playerResearch->getResearch()->getId() === $researchId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public function getOperationLog(): array
    {
        return $this->operationLog;
    }

    protected function addToOperationLog(string $log): void
    {
        $this->operationLog[] = $log;
    }
}
