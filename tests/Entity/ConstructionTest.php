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
    public function testCompletionTimestampFollowsTimestampAndDuration(): void
    {
        $construction = Construction::create(new WorldRegion(), new Player(), GameUnitEnum::FARM, 10, 3600);
        self::assertSame($construction->getTimestamp() + 3600, $construction->getCompletionTimestamp());

        $construction->setTimestamp(1_700_000_000);
        self::assertSame(1_700_003_600, $construction->getCompletionTimestamp());

        $construction->setDuration(60);
        self::assertSame(1_700_000_060, $construction->getCompletionTimestamp());
    }
}
