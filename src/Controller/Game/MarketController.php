<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use FrankProjects\UltimateWarfare\Service\Action\MarketActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class MarketController extends BaseGameController
{
    private MarketItemRepository $marketItemRepository;
    private MarketActionService $marketActionService;

    public function __construct(
        MarketItemRepository $marketItemRepository,
        MarketActionService $marketActionService
    ) {
        $this->marketItemRepository = $marketItemRepository;
        $this->marketActionService = $marketActionService;
    }

    public function marketBuyListApi(): JsonResponse
    {
        $player = $this->getPlayer();
        if (!$player->getWorld()->getMarket()) {
            return new JsonResponse(['success' => false, 'message' => 'Market not enabled']);
        }

        $marketItems = $this->marketItemRepository->findByWorldMarketItemType(
            $player->getWorld(),
            MarketItem::TYPE_SELL
        );

        $items = [];
        foreach ($marketItems as $item) {
            $items[] = [
                'id' => $item->getId(),
                'resource' => $item->getGameResource(),
                'amount' => $item->getAmount(),
                'price' => $item->getPrice(),
                'playerName' => $item->getPlayer()->getName(),
                'isOwn' => $item->getPlayer()->getId() === $player->getId(),
            ];
        }

        return new JsonResponse(['success' => true, 'items' => $items]);
    }

    public function marketSellListApi(): JsonResponse
    {
        $player = $this->getPlayer();
        if (!$player->getWorld()->getMarket()) {
            return new JsonResponse(['success' => false, 'message' => 'Market not enabled']);
        }

        $marketItems = $this->marketItemRepository->findByWorldMarketItemType(
            $player->getWorld(),
            MarketItem::TYPE_BUY
        );

        $items = [];
        foreach ($marketItems as $item) {
            $items[] = [
                'id' => $item->getId(),
                'resource' => $item->getGameResource(),
                'amount' => $item->getAmount(),
                'price' => $item->getPrice(),
                'playerName' => $item->getPlayer()->getName(),
                'isOwn' => $item->getPlayer()->getId() === $player->getId(),
            ];
        }

        return new JsonResponse(['success' => true, 'items' => $items]);
    }

    public function marketMyOrdersApi(): JsonResponse
    {
        $player = $this->getPlayer();
        if (!$player->getWorld()->getMarket()) {
            return new JsonResponse(['success' => false, 'message' => 'Market not enabled']);
        }

        $items = [];
        foreach ($player->getMarketItems() as $item) {
            $items[] = [
                'id' => $item->getId(),
                'resource' => $item->getGameResource(),
                'amount' => $item->getAmount(),
                'price' => $item->getPrice(),
                'type' => $item->getType(),
            ];
        }

        return new JsonResponse(['success' => true, 'items' => $items]);
    }

    public function marketActionApi(string $action, int $marketItemId): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            match ($action) {
                'buy' => $this->marketActionService->buyOrder($player, $marketItemId),
                'sell' => $this->marketActionService->sellOrder($player, $marketItemId),
                'cancel' => $this->marketActionService->cancelOrder($player, $marketItemId),
            };

            return new JsonResponse(['success' => true, 'message' => 'Order processed successfully']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function marketCreateOrderApi(Request $request): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                throw new \RuntimeException('Invalid request data');
            }
            $resource = isset($data['resource']) && is_string($data['resource']) ? $data['resource'] : '';
            $price = isset($data['price']) && is_int($data['price']) ? $data['price'] : 0;
            $amount = isset($data['amount']) && is_int($data['amount']) ? $data['amount'] : 0;
            $type = isset($data['type']) && is_string($data['type']) ? $data['type'] : '';
            $this->marketActionService->createOrder($player, $resource, $price, $amount, $type);

            return new JsonResponse(['success' => true, 'message' => 'Order created successfully']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
