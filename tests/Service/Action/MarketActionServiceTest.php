<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Service\Action\MarketActionService;
use FrankProjects\UltimateWarfare\Tests\Service\CreatesGameEngine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MarketActionServiceTest extends TestCase
{
    use CreatesGameEngine;

    private MarketItemRepository&MockObject $marketItemRepository;
    private PlayerRepository&MockObject $playerRepository;
    private World $world;

    protected function setUp(): void
    {
        $this->marketItemRepository = $this->createMock(MarketItemRepository::class);
        $this->playerRepository = $this->createMock(PlayerRepository::class);

        $this->world = new World();
        $this->world->setId(1);
        $this->world->setMarket(true);
    }

    public function testBuyOrderFailsWhenOrderWasTakenConcurrently(): void
    {
        $seller = $this->createPlayer(1, 0, 0);
        $buyer = $this->createPlayer(2, 1000, 0);
        $marketItem = MarketItem::createForPlayer(
            $seller,
            GameResource::GAME_RESOURCE_WOOD,
            100,
            500,
            MarketItem::TYPE_SELL
        );
        $this->marketItemRepository->method('find')->willReturn($marketItem);

        // The order row is gone once the lock is acquired: another buyer got there first
        $entityLockRepository = $this->createMock(EntityLockRepository::class);
        $entityLockRepository->method('lockForUpdate')->willReturn(false);

        $this->marketItemRepository->expects(self::never())->method('remove');
        $this->playerRepository->expects(self::never())->method('save');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Market order does not exist!');

        try {
            $this->createService($entityLockRepository)->buyOrder($buyer, 1);
        } finally {
            self::assertSame(1000, $buyer->getResources()->getCash());
            self::assertSame(0, $seller->getResources()->getCash());
        }
    }

    public function testCancelOrderFailsWhenOrderWasTakenConcurrently(): void
    {
        $player = $this->createPlayer(1, 0, 0);
        $marketItem = MarketItem::createForPlayer(
            $player,
            GameResource::GAME_RESOURCE_WOOD,
            100,
            500,
            MarketItem::TYPE_BUY
        );
        $this->marketItemRepository->method('find')->willReturn($marketItem);

        $entityLockRepository = $this->createMock(EntityLockRepository::class);
        $entityLockRepository->method('lockForUpdate')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Market order does not exist!');

        try {
            $this->createService($entityLockRepository)->cancelOrder($player, 1);
        } finally {
            // No refund of the escrowed cash for an order that is already gone
            self::assertSame(0, $player->getResources()->getCash());
        }
    }

    public function testSellOrderFailsWhenSellerLacksResources(): void
    {
        $buyer = $this->createPlayer(1, 0, 0);
        $seller = $this->createPlayer(2, 0, 50);
        $marketItem = MarketItem::createForPlayer(
            $buyer,
            GameResource::GAME_RESOURCE_WOOD,
            100,
            500,
            MarketItem::TYPE_BUY
        );
        $this->marketItemRepository->method('find')->willReturn($marketItem);

        $this->marketItemRepository->expects(self::never())->method('remove');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You do not have enough wood!');

        try {
            $this->createService()->sellOrder($seller, 1);
        } finally {
            self::assertSame(50, $seller->getResources()->getWood());
            self::assertSame(0, $seller->getResources()->getCash());
        }
    }

    public function testSellOrderPaysSellerFromEscrowAndDeliversResourcesToBuyer(): void
    {
        // The buyer's 500 cash was escrowed when the buy order was created, leaving them with 0
        $buyer = $this->createPlayer(1, 0, 0);
        $seller = $this->createPlayer(2, 0, 150);
        $marketItem = MarketItem::createForPlayer(
            $buyer,
            GameResource::GAME_RESOURCE_WOOD,
            100,
            500,
            MarketItem::TYPE_BUY
        );
        $this->marketItemRepository->method('find')->willReturn($marketItem);

        $this->marketItemRepository->expects(self::once())->method('remove')->with($marketItem);

        $this->createService()->sellOrder($seller, 1);

        self::assertSame(500, $seller->getResources()->getCash());
        self::assertSame(50, $seller->getResources()->getWood());
        self::assertSame(0, $buyer->getResources()->getCash());
        self::assertSame(100, $buyer->getResources()->getWood());
    }

    private function createService(?EntityLockRepository $entityLockRepository = null): MarketActionService
    {
        return new MarketActionService(
            $this->marketItemRepository,
            $this->playerRepository,
            $this->createMock(ReportRepository::class),
            $this->createGameEngine(null, null, null, $entityLockRepository)
        );
    }

    private function createPlayer(int $id, int $cash, int $wood): Player
    {
        $player = new Player();
        $player->setId($id);
        $player->setWorld($this->world);
        $player->setTimestampUpdate(time());
        $player->getResources()->setCash($cash);
        $player->getResources()->setWood($wood);

        return $player;
    }
}
