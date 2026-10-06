<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use FrankProjects\UltimateWarfare\Entity\Operation\AdvancedSpyOperation;
use FrankProjects\UltimateWarfare\Entity\Operation\SpyOperation;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\OperationRegistry;
use PHPUnit\Framework\TestCase;

class OperationRegistryTest extends TestCase
{
    private OperationRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new OperationRegistry();
    }

    public function testFindReturnsOperationBySlug(): void
    {
        $operation = $this->registry->find('spy');
        self::assertNotNull($operation);
        self::assertInstanceOf(SpyOperation::class, $operation);
    }

    public function testFindReturnsNullForUnknownSlug(): void
    {
        self::assertNull($this->registry->find('nonexistent'));
    }

    public function testFindAllReturnsAllOperations(): void
    {
        $operations = $this->registry->findAll();
        self::assertCount(10, $operations);
    }

    public function testFindEnabledReturnsOnlyEnabledOperations(): void
    {
        $operations = $this->registry->findEnabled();

        foreach ($operations as $operation) {
            self::assertTrue($operation->isEnabled());
        }
    }

    public function testFindAvailableForPlayerReturnsBaseOpsAtLowestLevel(): void
    {
        $player = $this->createPlayerWithResearch('spy-technology', 1);

        $operations = $this->registry->findAvailableForPlayer($player);
        $slugs = array_map(static fn ($o) => $o->getSlug(), $operations);

        self::assertContains('spy', $slugs);
        self::assertNotContains('advanced-spy', $slugs);
    }

    public function testFindAvailableForPlayerUnlocksAdvancedOpAtHigherLevel(): void
    {
        $player = $this->createPlayerWithResearch('spy-technology', 2);

        $operations = $this->registry->findAvailableForPlayer($player);
        $slugs = array_map(static fn ($o) => $o->getSlug(), $operations);

        self::assertContains('spy', $slugs);
        self::assertContains('advanced-spy', $slugs);

        // Sanity check the advanced op is wired to the consolidated research
        self::assertSame('spy-technology', (new AdvancedSpyOperation())->getResearchSlug());
    }

    public function testFindAvailableForPlayerReturnsEmptyWhenNoResearch(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getPlayerResearch')->willReturn(new ArrayCollection());

        $operations = $this->registry->findAvailableForPlayer($player);

        self::assertEmpty($operations);
    }

    public function testFindAvailableForPlayerExcludesInProgressResearch(): void
    {
        $researchPlayer = $this->createMock(ResearchPlayer::class);
        $researchPlayer->method('getActive')->willReturn(false);
        $researchPlayer->method('getResearchSlug')->willReturn('spy-technology');
        $researchPlayer->method('getLevel')->willReturn(1);

        $player = $this->createMock(Player::class);
        $player->method('getPlayerResearch')->willReturn(new ArrayCollection([$researchPlayer]));

        $operations = $this->registry->findAvailableForPlayer($player);

        self::assertEmpty($operations);
    }

    private function createPlayerWithResearch(string $slug, int $maxLevel): Player
    {
        $rows = [];
        for ($level = 1; $level <= $maxLevel; $level++) {
            $rp = $this->createMock(ResearchPlayer::class);
            $rp->method('getActive')->willReturn(true);
            $rp->method('getResearchSlug')->willReturn($slug);
            $rp->method('getLevel')->willReturn($level);
            $rows[] = $rp;
        }

        $player = $this->createMock(Player::class);
        $player->method('getPlayerResearch')->willReturn(new ArrayCollection($rows));

        return $player;
    }
}
