<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Entity\Research\AdvancedOpticsResearch;
use FrankProjects\UltimateWarfare\Entity\Research\AdvancedWoodProcessingResearch;
use FrankProjects\UltimateWarfare\Entity\Research\ArtilleryBombardmentResearch;
use FrankProjects\UltimateWarfare\Entity\Research\BallisticMissileTechnologyResearch;
use FrankProjects\UltimateWarfare\Entity\Research\BomberAttackResearch;
use FrankProjects\UltimateWarfare\Entity\Research\CounterEspionageResearch;
use FrankProjects\UltimateWarfare\Entity\Research\DefensiveNetworkResearch;
use FrankProjects\UltimateWarfare\Entity\Research\EfficientBuildingTechnologyResearch;
use FrankProjects\UltimateWarfare\Entity\Research\FactoryBlueprintResearch;
use FrankProjects\UltimateWarfare\Entity\Research\NavalBombardmentResearch;
use FrankProjects\UltimateWarfare\Entity\Research\NuclearTechnologyResearch;
use FrankProjects\UltimateWarfare\Entity\Research\OreExtractionImprovementsResearch;
use FrankProjects\UltimateWarfare\Entity\Research\RadarTechnologyResearch;
use FrankProjects\UltimateWarfare\Entity\Research\ResearchTierResearch;
use FrankProjects\UltimateWarfare\Entity\Research\SpyTechnologyResearch;
use FrankProjects\UltimateWarfare\Entity\Research\StrategicBomberAttackResearch;
use FrankProjects\UltimateWarfare\Entity\Research\SubmarineTechnologyResearch;

final class ResearchRegistry
{
    /** @var array<string, Research> */
    private array $researches;

    public function __construct()
    {
        $researchList = [
            new ResearchTierResearch(),
            new SpyTechnologyResearch(),
            new CounterEspionageResearch(),
            new DefensiveNetworkResearch(),
            new OreExtractionImprovementsResearch(),
            new AdvancedWoodProcessingResearch(),
            new EfficientBuildingTechnologyResearch(),
            new NuclearTechnologyResearch(),
            new FactoryBlueprintResearch(),
            new AdvancedOpticsResearch(),
            new SubmarineTechnologyResearch(),
            new BallisticMissileTechnologyResearch(),
            new RadarTechnologyResearch(),
            new NavalBombardmentResearch(),
            new ArtilleryBombardmentResearch(),
            new BomberAttackResearch(),
            new StrategicBomberAttackResearch(),
        ];

        $this->researches = [];
        foreach ($researchList as $research) {
            $this->researches[$research->getSlug()] = $research;
        }
    }

    public function find(string $slug): ?Research
    {
        return $this->researches[$slug] ?? null;
    }

    /**
     * @return Research[]
     */
    public function findAll(): array
    {
        return array_values($this->researches);
    }

    /**
     * @return Research[]
     */
    public function findEnabled(): array
    {
        return array_values(
            array_filter($this->researches, static fn (Research $r): bool => $r->isEnabled())
        );
    }

    /**
     * Returns researches whose next upgrade level is unlocked for the player.
     *
     * @param array<string,int> $completedLevelsBySlug Highest completed level per research slug.
     * @return Research[]
     */
    public function findAvailableForPlayer(array $completedLevelsBySlug): array
    {
        return array_values(
            array_filter($this->researches, static function (Research $research) use ($completedLevelsBySlug): bool {
                if (!$research->isEnabled()) {
                    return false;
                }

                $currentLevel = $completedLevelsBySlug[$research->getSlug()] ?? 0;
                $targetLevel = $currentLevel + 1;

                if ($targetLevel > $research->getMaxLevel()) {
                    return false;
                }

                foreach ($research->getPrerequisites($targetLevel) as $prereqClass => $minLevel) {
                    $prereqSlug = (new $prereqClass())->getSlug();
                    $playerLevel = $completedLevelsBySlug[$prereqSlug] ?? 0;
                    if ($playerLevel < $minLevel) {
                        return false;
                    }
                }

                return true;
            })
        );
    }
}
