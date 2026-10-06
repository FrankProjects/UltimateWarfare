<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Entity\Enum;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use PHPUnit\Framework\TestCase;

class GameUnitCategoryTest extends TestCase
{
    public function testOnlyDefenseAndSpecialBuildingsAreLeveled(): void
    {
        self::assertTrue(GameUnitCategory::DEFENSE_BUILDINGS->isLeveled());
        self::assertTrue(GameUnitCategory::SPECIAL_BUILDINGS->isLeveled());

        self::assertFalse(GameUnitCategory::BUILDINGS->isLeveled());
        self::assertFalse(GameUnitCategory::TROOPS->isLeveled());
        self::assertFalse(GameUnitCategory::NAVAL_UNITS->isLeveled());
        self::assertFalse(GameUnitCategory::AIR_UNITS->isLeveled());
        self::assertFalse(GameUnitCategory::MISSILES->isLeveled());
    }
}
