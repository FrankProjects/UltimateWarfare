<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Form\Admin;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;
use FrankProjects\UltimateWarfare\Form\Admin\World\MapConfigurationType;
use FrankProjects\UltimateWarfare\Form\Admin\WorldType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * These forms map onto int setters, which are called before validation runs.
 * Empty or malformed input must end up as a validation error, never as a TypeError.
 */
final class WorldFormsTest extends TypeTestCase
{
    /**
     * @return array<\Symfony\Component\Form\FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function validWorld(): array
    {
        return [
            'name' => 'Test world',
            'description' => 'Test description',
            'status' => '1',
            'maxPlayers' => '50',
            'federationLimit' => '5',
            'resources' => self::validResources(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function validResources(): array
    {
        return ['cash' => '1000', 'food' => '100', 'wood' => '100', 'steel' => '100'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function validMapConfiguration(): array
    {
        return [
            'seed' => '12345',
            'size' => '25',
            'deepWaterLevel' => '130',
            'waterLevel' => '150',
            'shallowWaterLevel' => '160',
            'sandLevel' => '168',
            'grasslandLevel' => '200',
            'forestLevel' => '220',
            'hillsLevel' => '225',
        ];
    }

    public function testWorldMapsValidSubmission(): void
    {
        $world = new World();
        $form = $this->factory->create(WorldType::class, $world);

        $form->submit(self::validWorld());

        self::assertTrue($form->isValid());
        self::assertSame(1, $world->getStatus());
        self::assertSame(50, $world->getMaxPlayers());
        self::assertSame(5, $world->getFederationLimit());
        self::assertSame(1000, $world->getResources()->getCash());
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidWorldProvider(): array
    {
        return [
            'nothing submitted' => [[]],
            'empty max players' => [['maxPlayers' => ''] + self::validWorld()],
            'decimal max players' => [['maxPlayers' => '10.5'] + self::validWorld()],
            'zero federation limit' => [['federationLimit' => '0'] + self::validWorld()],
            'negative cash' => [['resources' => ['cash' => '-1'] + self::validResources()] + self::validWorld()],
        ];
    }

    /**
     * @param array<string, mixed> $submitted
     */
    #[DataProvider('invalidWorldProvider')]
    public function testWorldRejectsInvalidSubmission(array $submitted): void
    {
        $form = $this->factory->create(WorldType::class, new World());

        $form->submit($submitted);

        self::assertFalse($form->isValid());
    }

    public function testWorldEmptyStatusMeansCreated(): void
    {
        $world = new World();
        $world->setStatus(2);
        $form = $this->factory->create(WorldType::class, $world);

        $form->submit(['status' => ''] + self::validWorld());

        self::assertTrue($form->isValid());
        self::assertSame(0, $world->getStatus());
    }

    public function testWorldEmptyResourceMeansZero(): void
    {
        $world = new World();
        $form = $this->factory->create(WorldType::class, $world);

        $form->submit(['resources' => ['cash' => ''] + self::validResources()] + self::validWorld());

        self::assertTrue($form->isValid());
        self::assertSame(0, $world->getResources()->getCash());
    }

    public function testMapConfigurationMapsValidSubmission(): void
    {
        $mapConfiguration = new MapConfiguration();
        $form = $this->factory->create(MapConfigurationType::class, $mapConfiguration);

        $form->submit(self::validMapConfiguration());

        self::assertTrue($form->isValid());
        self::assertSame(12345, $mapConfiguration->getSeed());
        self::assertSame(25, $mapConfiguration->getSize());
        self::assertSame(130, $mapConfiguration->getDeepWaterLevel());
    }

    public function testMapConfigurationEmptySeedMeansRandom(): void
    {
        $mapConfiguration = new MapConfiguration();
        $form = $this->factory->create(MapConfigurationType::class, $mapConfiguration);

        $form->submit(['seed' => ''] + self::validMapConfiguration());

        self::assertTrue($form->isValid());
        self::assertSame(0, $mapConfiguration->getSeed());
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidMapConfigurationProvider(): array
    {
        return [
            'nothing submitted' => [[]],
            'non-numeric size' => [['size' => 'abc'] + self::validMapConfiguration()],
            'empty size' => [['size' => ''] + self::validMapConfiguration()],
            'non-numeric seed' => [['seed' => 'abc'] + self::validMapConfiguration()],
            'empty level' => [['waterLevel' => ''] + self::validMapConfiguration()],
            'non-numeric level' => [['waterLevel' => 'abc'] + self::validMapConfiguration()],
            'level out of range' => [['waterLevel' => '300'] + self::validMapConfiguration()],
        ];
    }

    /**
     * @param array<string, mixed> $submitted
     */
    #[DataProvider('invalidMapConfigurationProvider')]
    public function testMapConfigurationRejectsInvalidSubmission(array $submitted): void
    {
        $form = $this->factory->create(MapConfigurationType::class, new MapConfiguration());

        $form->submit($submitted);

        self::assertFalse($form->isValid());
    }
}
