<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Entity;

use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Operation\AdvancedSpyOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\ArtilleryBombardmentOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\BomberAttackOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\MissileAttackOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\NuclearMissileAttackOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\SniperAttackOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\SpyOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\StrategicBomberAttackOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\SubmarineAttackOperation;
use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    /**
     * @return array<string, array{class-string<Operation>, string}>
     */
    public static function operationProvider(): array
    {
        return [
            'spy' => [SpyOperation::class, 'spy'],
            'advanced-spy' => [AdvancedSpyOperation::class, 'advanced-spy'],
            'sniper-attack' => [SniperAttackOperation::class, 'sniper-attack'],
            'missile-attack' => [MissileAttackOperation::class, 'missile-attack'],
            'submarine-attack' => [SubmarineAttackOperation::class, 'submarine-attack'],
            'bomber-attack' => [BomberAttackOperation::class, 'bomber-attack'],
            'strategic-bomber-attack' => [StrategicBomberAttackOperation::class, 'strategic-bomber-attack'],
            'nuclear-missile-attack' => [NuclearMissileAttackOperation::class, 'nuclear-missile-attack'],
        ];
    }

    /**
     * @param class-string<Operation> $operationClass
     */
    #[DataProvider('operationProvider')]
    public function testGetSlugReturnsExpectedSlug(string $operationClass, string $expectedSlug): void
    {
        $operation = new $operationClass();
        self::assertSame($expectedSlug, $operation->getSlug());
    }

    /**
     * @param class-string<Operation> $operationClass
     */
    #[DataProvider('operationProvider')]
    public function testGetResearchClassReturnsValidResearchSubclass(string $operationClass): void
    {
        $operation = new $operationClass();
        $research = new ($operation->getResearchClass())();

        self::assertInstanceOf(Research::class, $research);
    }

    /**
     * @param class-string<Operation> $operationClass
     */
    #[DataProvider('operationProvider')]
    public function testGetResearchSlugDerivedFromClass(string $operationClass): void
    {
        $operation = new $operationClass();
        $researchClass = $operation->getResearchClass();
        $expectedSlug = (new $researchClass())->getSlug();

        self::assertSame($expectedSlug, $operation->getResearchSlug());
    }

    /**
     * @param class-string<Operation> $operationClass
     */
    #[DataProvider('operationProvider')]
    public function testGetResearchNameReturnsNonEmptyString(string $operationClass): void
    {
        $operation = new $operationClass();
        self::assertNotEmpty($operation->getResearchName());
    }

    /**
     * @param class-string<Operation> $operationClass
     */
    #[DataProvider('operationProvider')]
    public function testGetProcessorClassReturnsValidOperationInterface(string $operationClass): void
    {
        $operation = new $operationClass();
        $processorClass = $operation->getProcessorClass();

        self::assertTrue(class_exists($processorClass), "Processor class {$processorClass} does not exist");
        self::assertTrue(
            is_subclass_of($processorClass, OperationInterface::class),
            "{$processorClass} does not implement OperationInterface"
        );
    }

    /**
     * @param class-string<Operation> $operationClass
     */
    #[DataProvider('operationProvider')]
    public function testOperationHasValidProperties(string $operationClass): void
    {
        $operation = new $operationClass();

        self::assertNotEmpty($operation->getName());
        self::assertNotEmpty($operation->getImage());
        self::assertNotEmpty($operation->getDescription());
        self::assertGreaterThan(0, $operation->getCost());
        self::assertGreaterThanOrEqual(0.0, $operation->getDifficulty());
        self::assertLessThanOrEqual(1.0, $operation->getDifficulty());
        self::assertGreaterThan(0, $operation->getMaxDistance());
        if ($operation->getGameUnit() !== null) {
            self::assertGreaterThan(0, $operation->getGameUnit()->value);
        }
    }

    public function testAllOperationsHaveUniqueSlugs(): void
    {
        $operations = array_map(
            static fn (array $data): Operation => new $data[0](),
            self::operationProvider()
        );

        $slugs = array_map(
            static fn (Operation $o): string => $o->getSlug(),
            $operations
        );

        self::assertCount(count($slugs), array_unique($slugs), 'Duplicate operation slugs found');
    }

    public function testSpyOperationSpecificProperties(): void
    {
        $operation = new SpyOperation();

        self::assertSame('Spy Operation', $operation->getName());
        self::assertSame(5000, $operation->getCost());
        self::assertSame(0.1, $operation->getDifficulty());
        self::assertSame(3, $operation->getMaxDistance());
        self::assertNull($operation->getGameUnit());
        self::assertTrue($operation->isEnabled());
        self::assertSame(1, $operation->getResearchMinLevel());
    }

    public function testAdvancedSpyOperationRequiresHigherSpyTechnologyLevel(): void
    {
        self::assertSame(2, (new AdvancedSpyOperation())->getResearchMinLevel());
        self::assertSame('spy-technology', (new AdvancedSpyOperation())->getResearchSlug());
    }

    public function testSpecialOperationResearchSlugs(): void
    {
        self::assertSame(1, (new ArtilleryBombardmentOperation())->getResearchMinLevel());
        self::assertSame(
            'special-operation-artillery-bombardment',
            (new ArtilleryBombardmentOperation())->getResearchSlug()
        );

        self::assertSame(1, (new BomberAttackOperation())->getResearchMinLevel());
        self::assertSame(
            'special-operation-bomber-attack',
            (new BomberAttackOperation())->getResearchSlug()
        );

        self::assertSame(1, (new StrategicBomberAttackOperation())->getResearchMinLevel());
        self::assertSame(
            'special-operation-strategic-bomber-attack',
            (new StrategicBomberAttackOperation())->getResearchSlug()
        );
    }
}
