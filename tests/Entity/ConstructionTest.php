<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Entity;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use PHPUnit\Framework\TestCase;

class ConstructionTest extends TestCase
{
    public function testCreateCompletesAfterTheBuildDuration(): void
    {
        $before = time();
        $construction = Construction::create(new WorldRegion(), new Player(), GameUnitEnum::FARM, 10, 3600);

        self::assertGreaterThanOrEqual($before + 3600, $construction->getCompletionTimestamp());
        self::assertLessThanOrEqual(time() + 3600, $construction->getCompletionTimestamp());
    }
}
