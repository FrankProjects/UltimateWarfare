<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Entity;

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
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResearchTest extends TestCase
{
    /**
     * @return array<string, array{class-string<Research>}>
     */
    public static function researchProvider(): array
    {
        return [
            'research-tier' => [ResearchTierResearch::class],
            'spy-technology' => [SpyTechnologyResearch::class],
            'counter-espionage' => [CounterEspionageResearch::class],
            'defensive-network' => [DefensiveNetworkResearch::class],
            'ore-extraction-improvements' => [OreExtractionImprovementsResearch::class],
            'advanced-wood-processing' => [AdvancedWoodProcessingResearch::class],
            'efficient-building-technology' => [EfficientBuildingTechnologyResearch::class],
            'nuclear-technology' => [NuclearTechnologyResearch::class],
            'factory-blueprint' => [FactoryBlueprintResearch::class],
            'advanced-optics' => [AdvancedOpticsResearch::class],
            'submarine-technology' => [SubmarineTechnologyResearch::class],
            'ballistic-missile-technology' => [BallisticMissileTechnologyResearch::class],
            'radar-technology' => [RadarTechnologyResearch::class],
            'naval-bombardment' => [NavalBombardmentResearch::class],
            'special-operation-artillery-bombardment' => [ArtilleryBombardmentResearch::class],
            'special-operation-bomber-attack' => [BomberAttackResearch::class],
            'special-operation-strategic-bomber-attack' => [StrategicBomberAttackResearch::class],
        ];
    }

    /**
     * @param class-string<Research> $researchClass
     */
    #[DataProvider('researchProvider')]
    public function testGetSlugReturnsNonEmptyString(string $researchClass): void
    {
        $research = new $researchClass();
        self::assertNotEmpty($research->getSlug());
    }

    /**
     * @param class-string<Research> $researchClass
     */
    #[DataProvider('researchProvider')]
    public function testResearchHasValidProperties(string $researchClass): void
    {
        $research = new $researchClass();

        self::assertNotEmpty($research->getName());
        self::assertNotEmpty($research->getImage());
        self::assertNotEmpty($research->getDescription());
        self::assertGreaterThan(0, $research->getMaxLevel());

        for ($level = 1; $level <= $research->getMaxLevel(); $level++) {
            self::assertGreaterThan(0, $research->getCost($level));
            self::assertGreaterThan(0, $research->getTimestamp($level));
        }
    }

    /**
     * @param class-string<Research> $researchClass
     */
    #[DataProvider('researchProvider')]
    public function testPrerequisitesReferenceValidResearchClasses(string $researchClass): void
    {
        $research = new $researchClass();
        self::assertGreaterThan(0, $research->getMaxLevel());

        for ($level = 1; $level <= $research->getMaxLevel(); $level++) {
            foreach ($research->getPrerequisites($level) as $prereqClass => $minLevel) {
                self::assertGreaterThan(0, $minLevel);
                $prerequisite = new $prereqClass();
                self::assertInstanceOf(Research::class, $prerequisite);
                self::assertGreaterThanOrEqual($minLevel, $prerequisite->getMaxLevel());
            }
        }
    }

    public function testInvalidLevelThrows(): void
    {
        $research = new ResearchTierResearch();

        $this->expectException(InvalidArgumentException::class);
        $research->getCost($research->getMaxLevel() + 1);
    }

    public function testAllResearchHaveUniqueSlugs(): void
    {
        $researches = array_map(
            static fn (array $data): Research => new $data[0](),
            self::researchProvider()
        );

        $slugs = array_map(
            static fn (Research $r): string => $r->getSlug(),
            $researches
        );

        self::assertCount(count($slugs), array_unique($slugs), 'Duplicate research slugs found');
    }

    public function testResearchTierLevel1HasNoPrerequisites(): void
    {
        $research = new ResearchTierResearch();
        self::assertEmpty($research->getPrerequisites(1));
    }

    public function testResearchTierHasFiveLevels(): void
    {
        self::assertSame(5, (new ResearchTierResearch())->getMaxLevel());
    }

    public function testSpyTechnologyLevel1RequiresResearchTier2(): void
    {
        $research = new SpyTechnologyResearch();
        self::assertSame(
            [ResearchTierResearch::class => 2],
            $research->getPrerequisites(1)
        );
    }

    public function testStrategicBomberAttackRequiresResearchTier4(): void
    {
        $research = new StrategicBomberAttackResearch();
        self::assertSame(
            [ResearchTierResearch::class => 4],
            $research->getPrerequisites(1)
        );
    }

    public function testGetPrerequisiteDescriptionsReturnsSlugAndName(): void
    {
        $research = new ArtilleryBombardmentResearch();
        $descriptions = $research->getPrerequisiteDescriptions(1);

        self::assertCount(1, $descriptions);
        self::assertSame('research-tier', $descriptions[0]['slug']);
        self::assertSame('Research Tier', $descriptions[0]['name']);
        self::assertSame(2, $descriptions[0]['minLevel']);
    }
}
