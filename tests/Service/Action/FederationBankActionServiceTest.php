<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\Federation\Resources as FederationResources;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources as PlayerResources;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\Action\FederationBankActionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FederationBankActionServiceTest extends TestCase
{
    private FederationBankActionService $service;
    private FederationRepository&MockObject $federationRepository;
    private FederationNewsRepository&MockObject $federationNewsRepository;
    private PlayerRepository&MockObject $playerRepository;

    protected function setUp(): void
    {
        $this->federationRepository = $this->createMock(FederationRepository::class);
        $this->federationNewsRepository = $this->createMock(FederationNewsRepository::class);
        $this->playerRepository = $this->createMock(PlayerRepository::class);

        $this->service = new FederationBankActionService(
            $this->federationRepository,
            $this->federationNewsRepository,
            $this->playerRepository
        );
    }

    private function createPlayer(
        int $cash = 1000,
        int $wood = 500,
        int $steel = 200,
        int $food = 300,
        int $hierarchy = Player::FEDERATION_HIERARCHY_GENERAL
    ): Player&MockObject {
        $world = $this->createMock(World::class);
        $world->method('getFederation')->willReturn(true);

        $playerResources = new PlayerResources();
        $playerResources->setCash($cash);
        $playerResources->setWood($wood);
        $playerResources->setSteel($steel);
        $playerResources->setFood($food);

        $player = $this->createMock(Player::class);
        $player->method('getResources')->willReturn($playerResources);
        $player->method('getWorld')->willReturn($world);
        $player->method('getName')->willReturn('TestPlayer');
        $player->method('getFederationHierarchy')->willReturn($hierarchy);

        return $player;
    }

    private function createFederation(
        int $cash = 5000,
        int $wood = 2000,
        int $steel = 1000,
        int $food = 1500
    ): Federation {
        $fedResources = new FederationResources();
        $fedResources->setCash($cash);
        $fedResources->setWood($wood);
        $fedResources->setSteel($steel);
        $fedResources->setFood($food);

        $federation = $this->createMock(Federation::class);
        $federation->method('getResources')->willReturn($fedResources);

        return $federation;
    }

    // ========== DEPOSIT TESTS ==========

    public function testDepositTransfersResources(): void
    {
        $federation = $this->createFederation(5000, 2000, 1000, 1500);
        $player = $this->createPlayer(1000, 500, 200, 300);
        $player->method('getFederation')->willReturn($federation);

        $this->playerRepository->expects(self::once())->method('save');
        $this->federationRepository->expects(self::once())->method('save');
        $this->federationNewsRepository->expects(self::once())->method('save');

        $this->service->deposit($player, ['cash' => '400', 'wood' => '100']);

        self::assertEquals(600, $player->getResources()->getCash());
        self::assertEquals(400, $player->getResources()->getWood());
        self::assertEquals(5400, $federation->getResources()->getCash());
        self::assertEquals(2100, $federation->getResources()->getWood());
    }

    public function testDepositAllResourceTypes(): void
    {
        $federation = $this->createFederation(0, 0, 0, 0);
        $player = $this->createPlayer(1000, 500, 200, 300);
        $player->method('getFederation')->willReturn($federation);

        $this->service->deposit($player, [
            'cash' => '100',
            'wood' => '50',
            'steel' => '25',
            'food' => '10',
        ]);

        self::assertEquals(900, $player->getResources()->getCash());
        self::assertEquals(450, $player->getResources()->getWood());
        self::assertEquals(175, $player->getResources()->getSteel());
        self::assertEquals(290, $player->getResources()->getFood());
        self::assertEquals(100, $federation->getResources()->getCash());
        self::assertEquals(50, $federation->getResources()->getWood());
        self::assertEquals(25, $federation->getResources()->getSteel());
        self::assertEquals(10, $federation->getResources()->getFood());
    }

    public function testDepositFailsWhenNotEnoughResources(): void
    {
        $federation = $this->createFederation();
        $player = $this->createPlayer(100, 500, 200, 300);
        $player->method('getFederation')->willReturn($federation);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("You don't have enough cash!");

        $this->service->deposit($player, ['cash' => '500']);
    }

    public function testDepositFailsWithNoFederation(): void
    {
        $player = $this->createPlayer();
        $player->method('getFederation')->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You are not in a Federation!');

        $this->service->deposit($player, ['cash' => '100']);
    }

    public function testDepositFailsWhenFederationsDisabled(): void
    {
        $world = $this->createMock(World::class);
        $world->method('getFederation')->willReturn(false);

        $player = $this->createMock(Player::class);
        $player->method('getWorld')->willReturn($world);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Federations not enabled!');

        $this->service->deposit($player, ['cash' => '100']);
    }

    public function testDepositSkipsZeroAmounts(): void
    {
        $federation = $this->createFederation(5000, 2000, 1000, 1500);
        $player = $this->createPlayer(1000, 500, 200, 300);
        $player->method('getFederation')->willReturn($federation);

        $this->service->deposit($player, ['cash' => '100', 'wood' => '0']);

        self::assertEquals(900, $player->getResources()->getCash());
        self::assertEquals(500, $player->getResources()->getWood());
    }

    public function testDepositSkipsInvalidResourceNames(): void
    {
        $federation = $this->createFederation(5000, 2000, 1000, 1500);
        $player = $this->createPlayer(1000, 500, 200, 300);
        $player->method('getFederation')->willReturn($federation);

        $this->service->deposit($player, ['cash' => '100', 'diamonds' => '999']);

        self::assertEquals(900, $player->getResources()->getCash());
        self::assertEquals(5100, $federation->getResources()->getCash());
    }

    public function testDepositFailsWithNoResourcesSelected(): void
    {
        $federation = $this->createFederation();
        $player = $this->createPlayer();
        $player->method('getFederation')->willReturn($federation);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resources selected to deposit');

        $this->service->deposit($player, []);
    }

    // ========== WITHDRAW TESTS ==========

    public function testWithdrawTransfersResources(): void
    {
        $federation = $this->createFederation(5000, 2000, 1000, 1500);
        $player = $this->createPlayer(100, 50, 20, 30);
        $player->method('getFederation')->willReturn($federation);

        $this->playerRepository->expects(self::once())->method('save');
        $this->federationRepository->expects(self::once())->method('save');
        $this->federationNewsRepository->expects(self::once())->method('save');

        $this->service->withdraw($player, ['cash' => '500', 'steel' => '200']);

        self::assertEquals(600, $player->getResources()->getCash());
        self::assertEquals(220, $player->getResources()->getSteel());
        self::assertEquals(4500, $federation->getResources()->getCash());
        self::assertEquals(800, $federation->getResources()->getSteel());
    }

    public function testWithdrawFailsWhenNotEnoughInBank(): void
    {
        $federation = $this->createFederation(100, 2000, 1000, 1500);
        $player = $this->createPlayer();
        $player->method('getFederation')->willReturn($federation);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Federation Bank doesn't have enough cash!");

        $this->service->withdraw($player, ['cash' => '500']);
    }

    public function testWithdrawFailsWithNoFederation(): void
    {
        $player = $this->createPlayer();
        $player->method('getFederation')->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You are not in a Federation!');

        $this->service->withdraw($player, ['cash' => '100']);
    }

    public function testWithdrawFailsWithInsufficientRank(): void
    {
        $federation = $this->createFederation();
        $player = $this->createPlayer(
            1000,
            500,
            200,
            300,
            Player::FEDERATION_HIERARCHY_RECRUIT
        );
        $player->method('getFederation')->willReturn($federation);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("You don't have permission to use the Federation Bank!");

        $this->service->withdraw($player, ['cash' => '100']);
    }

    public function testWithdrawAllowedForCaptainRank(): void
    {
        $federation = $this->createFederation(5000, 2000, 1000, 1500);
        $player = $this->createPlayer(
            100,
            50,
            20,
            30,
            Player::FEDERATION_HIERARCHY_CAPTAIN
        );
        $player->method('getFederation')->willReturn($federation);

        $this->service->withdraw($player, ['cash' => '100']);

        self::assertEquals(200, $player->getResources()->getCash());
        self::assertEquals(4900, $federation->getResources()->getCash());
    }

    public function testWithdrawDoesNotSaveWhenNoResourcesSelected(): void
    {
        $federation = $this->createFederation();
        $player = $this->createPlayer();
        $player->method('getFederation')->willReturn($federation);

        $this->playerRepository->expects(self::never())->method('save');
        $this->federationRepository->expects(self::never())->method('save');

        $this->service->withdraw($player, []);
    }

    public function testWithdrawSkipsNegativeAmounts(): void
    {
        $federation = $this->createFederation(5000, 2000, 1000, 1500);
        $player = $this->createPlayer(100, 50, 20, 30);
        $player->method('getFederation')->willReturn($federation);

        $this->service->withdraw($player, ['cash' => '100', 'wood' => '-50']);

        self::assertEquals(200, $player->getResources()->getCash());
        self::assertEquals(50, $player->getResources()->getWood());
    }
}
