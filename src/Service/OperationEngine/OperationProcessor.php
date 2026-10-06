<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine;

use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Util\ReportCreator;
use RuntimeException;

abstract class OperationProcessor implements OperationInterface
{
    protected WorldRegion $region;
    protected Operation $operation;
    protected WorldRegion $playerRegion;
    protected int $amount;
    protected ReportCreator $reportCreator;
    protected PlayerRepository $playerRepository;
    protected WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository;
    protected WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository;
    protected WorldRegionRepository $worldRegionRepository;
    protected ConstructionRepository $constructionRepository;
    protected GameUnitRegistry $gameUnitRegistry;
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $operationLog = [];

    private function __construct(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount,
        ReportCreator $reportCreator,
        PlayerRepository $playerRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository,
        GameUnitRegistry $gameUnitRegistry
    ) {
        $this->region = $region;
        $this->operation = $operation;
        $this->playerRegion = $playerRegion;
        $this->amount = $amount;
        $this->reportCreator = $reportCreator;
        $this->playerRepository = $playerRepository;
        $this->worldRegionStackableUnitRepository = $worldRegionStackableUnitRepository;
        $this->worldRegionLeveledUnitRepository = $worldRegionLeveledUnitRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->constructionRepository = $constructionRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
    }

    public static function factory(
        WorldRegion $region,
        Operation $operation,
        WorldRegion $playerRegion,
        int $amount,
        ReportCreator $reportCreator,
        PlayerRepository $playerRepository,
        WorldRegionStackableUnitRepository $worldRegionStackableUnitRepository,
        WorldRegionLeveledUnitRepository $worldRegionLeveledUnitRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionRepository $constructionRepository,
        GameUnitRegistry $gameUnitRegistry
    ): OperationInterface {
        $className = $operation->getProcessorClass();
        if (!class_exists($className) || is_subclass_of($className, OperationInterface::class) === false) {
            throw new RuntimeException("Unknown Operation processor {$className}");
        }

        return new $className(
            $region,
            $operation,
            $playerRegion,
            $amount,
            $reportCreator,
            $playerRepository,
            $worldRegionStackableUnitRepository,
            $worldRegionLeveledUnitRepository,
            $worldRegionRepository,
            $constructionRepository,
            $gameUnitRegistry
        );
    }

    /**
     * @return array<int, array<string, mixed>>
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

    protected function hasResearched(string $researchSlug): bool
    {
        foreach ($this->getPlayerRegionPlayer()->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() === false) {
                continue;
            }

            if ($playerResearch->getResearchSlug() === $researchSlug) {
                return true;
            }
        }

        return false;
    }

    protected function getAttackerResearchLevel(string $researchSlug): int
    {
        return $this->highestCompletedResearchLevel($this->getPlayerRegionPlayer(), $researchSlug);
    }

    protected function getTargetResearchLevel(string $researchSlug): int
    {
        $targetPlayer = $this->region->getPlayer();
        if ($targetPlayer === null) {
            return 0;
        }

        return $this->highestCompletedResearchLevel($targetPlayer, $researchSlug);
    }

    private function highestCompletedResearchLevel(Player $player, string $researchSlug): int
    {
        $highest = 0;
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() === false) {
                continue;
            }
            if ($playerResearch->getResearchSlug() !== $researchSlug) {
                continue;
            }
            if ($playerResearch->getLevel() > $highest) {
                $highest = $playerResearch->getLevel();
            }
        }

        return $highest;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOperationLog(): array
    {
        return $this->operationLog;
    }

    protected function addToOperationLog(string $log): void
    {
        $this->operationLog[] = ['type' => 'line', 'text' => $log];
    }

    protected function addSection(string $title): void
    {
        $this->operationLog[] = ['type' => 'section', 'title' => $title];
    }

    protected function addRow(string $label, string $value): void
    {
        $this->operationLog[] = ['type' => 'row', 'label' => $label, 'value' => $value];
    }

    protected function addEmpty(string $text): void
    {
        $this->operationLog[] = ['type' => 'empty', 'text' => $text];
    }

    protected function addReportEntry(int $timestamp, string $text): void
    {
        $this->operationLog[] = ['type' => 'report', 'timestamp' => $timestamp, 'text' => $text];
    }

    protected function addFailure(string $text): void
    {
        $this->operationLog[] = ['type' => 'failure', 'text' => $text];
    }

    protected function getTargetRegionPlayer(): Player
    {
        return $this->getWorldRegionPlayer($this->region);
    }

    protected function getPlayerRegionPlayer(): Player
    {
        return $this->getWorldRegionPlayer($this->playerRegion);
    }

    private function getWorldRegionPlayer(WorldRegion $worldRegion): Player
    {
        $player = $worldRegion->getPlayer();
        if ($player === null) {
            throw new RuntimeException("Region has no owner");
        }

        return $player;
    }
}
