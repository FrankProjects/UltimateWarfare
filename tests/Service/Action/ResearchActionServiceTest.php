<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use Doctrine\Common\Collections\ArrayCollection;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources as PlayerResources;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use FrankProjects\UltimateWarfare\Service\Action\ResearchActionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ResearchActionServiceTest extends TestCase
{
    private ResearchActionService $service;
    private ResearchRegistry $researchRegistry;
    private ResearchPlayerRepository&MockObject $researchPlayerRepository;
    private PlayerRepository&MockObject $playerRepository;

    protected function setUp(): void
    {
        $this->researchRegistry = new ResearchRegistry();
        $this->researchPlayerRepository = $this->createMock(ResearchPlayerRepository::class);
        $this->playerRepository = $this->createMock(PlayerRepository::class);

        $this->service = new ResearchActionService(
            $this->researchRegistry,
            $this->researchPlayerRepository,
            $this->playerRepository
        );
    }

    /**
     * @param ResearchPlayer[] $playerResearch
     */
    private function createPlayer(int $cash = 1000, array $playerResearch = []): Player
    {
        $playerResources = new PlayerResources();
        $playerResources->setCash($cash);

        $player = $this->createMock(Player::class);
        $player->method('getResources')->willReturn($playerResources);
        $player->method('getPlayerResearch')->willReturn(new ArrayCollection($playerResearch));

        return $player;
    }

    private function createCompletedResearch(string $slug, int $level): ResearchPlayer
    {
        $researchPlayer = new ResearchPlayer();
        $researchPlayer->setResearchSlug($slug);
        $researchPlayer->setLevel($level);
        $researchPlayer->setActive(true);
        $researchPlayer->setTimestamp(time());
        $researchPlayer->setCompletionTimestamp(time() - 1);

        return $researchPlayer;
    }

    private function createOngoingResearch(string $slug, int $level): ResearchPlayer
    {
        $researchPlayer = new ResearchPlayer();
        $researchPlayer->setResearchSlug($slug);
        $researchPlayer->setLevel($level);
        $researchPlayer->setActive(false);
        $researchPlayer->setTimestamp(time());
        $researchPlayer->setCompletionTimestamp(time() + 180);

        return $researchPlayer;
    }

    public function testPerformResearchStartsLevel1WhenPlayerHasNothing(): void
    {
        $player = $this->createPlayer(5000);

        $this->researchPlayerRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static fn (ResearchPlayer $rp): bool =>
                $rp->getResearchSlug() === 'research-tier' && $rp->getLevel() === 1));

        $this->service->performResearch('research-tier', $player);

        self::assertEquals(2500, $player->getResources()->getCash());
    }

    public function testPerformResearchUpgradesToNextLevel(): void
    {
        $level1 = $this->createCompletedResearch('research-tier', 1);
        $player = $this->createPlayer(60000, [$level1]);

        $this->researchPlayerRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static fn (ResearchPlayer $rp): bool =>
                $rp->getResearchSlug() === 'research-tier' && $rp->getLevel() === 2));

        $this->service->performResearch('research-tier', $player);

        self::assertEquals(10000, $player->getResources()->getCash());
    }

    public function testPerformResearchFailsWhenAtMaxLevel(): void
    {
        $completed = [];
        for ($level = 1; $level <= 5; $level++) {
            $completed[] = $this->createCompletedResearch('research-tier', $level);
        }

        $player = $this->createPlayer(1000000000, $completed);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This technology is already at its maximum level!');

        $this->service->performResearch('research-tier', $player);
    }

    public function testPerformResearchFailsWhenResearchNotFound(): void
    {
        $player = $this->createPlayer();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This technology does not exist!');

        $this->service->performResearch('nonexistent-research', $player);
    }

    public function testPerformResearchFailsWhenAnotherResearchInProgress(): void
    {
        $ongoing = $this->createOngoingResearch('research-tier', 2);
        $player = $this->createPlayer(100000, [$ongoing]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You can only research 1 technology at a time!');

        $this->service->performResearch('special-operation-artillery-bombardment', $player);
    }

    public function testPerformResearchFailsWhenPrerequisiteLevelNotMet(): void
    {
        $player = $this->createPlayer(100000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You do not have all required technologies!');

        $this->service->performResearch('special-operation-artillery-bombardment', $player);
    }

    public function testPerformResearchFailsWhenCannotAfford(): void
    {
        $player = $this->createPlayer(100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You can not afford that!');

        $this->service->performResearch('research-tier', $player);
    }

    public function testPerformResearchSucceedsWithPrerequisiteLevelMet(): void
    {
        $researchTier2 = $this->createCompletedResearch('research-tier', 2);
        $player = $this->createPlayer(50000, [$researchTier2]);

        $this->researchPlayerRepository->expects(self::once())->method('save');

        $this->service->performResearch('special-operation-artillery-bombardment', $player);

        self::assertEquals(25000, $player->getResources()->getCash());
    }

    public function testPerformCancelRemovesInProgressUpgrade(): void
    {
        $ongoing = $this->createOngoingResearch('research-tier', 2);
        $player = $this->createPlayer(0, [$ongoing]);

        $this->researchPlayerRepository->expects(self::once())->method('remove')->with($ongoing);

        $this->service->performCancel('research-tier', $player);
    }

    public function testPerformCancelDoesNotRemoveCompletedLevels(): void
    {
        $completed = $this->createCompletedResearch('research-tier', 1);
        $player = $this->createPlayer(0, [$completed]);

        $this->researchPlayerRepository->expects(self::never())->method('remove');

        $this->service->performCancel('research-tier', $player);
    }

    public function testPerformCancelFailsWhenResearchNotFound(): void
    {
        $player = $this->createPlayer();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This technology does not exist!');

        $this->service->performCancel('nonexistent-research', $player);
    }

    public function testPerformCancelDoesNothingWhenPlayerHasNoMatchingResearch(): void
    {
        $player = $this->createPlayer(0);

        $this->researchPlayerRepository->expects(self::never())->method('remove');

        $this->service->performCancel('research-tier', $player);
    }
}
