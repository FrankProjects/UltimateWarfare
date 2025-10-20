<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Form\Game\MarketOrderType;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use FrankProjects\UltimateWarfare\Service\Action\MarketActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public function buy(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render(
                'game/market/disabled.html.twig',
                [
                    'player' => $player
                ]
            );
        }

        $marketItems = $this->marketItemRepository->findByWorldMarketItemType(
            $player->getWorld(),
            MarketItem::TYPE_SELL
        );

        return $this->render(
            'game/market/buy.html.twig',
            [
                'player' => $player,
                'marketItems' => $marketItems
            ]
        );
    }

    public function buyOrder(int $marketItemId): RedirectResponse
    {
        try {
            $this->marketActionService->buyOrder($this->getPlayer(), $marketItemId);
            $this->addFlash('success', 'You bought something on the market!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Market');
    }

    public function sell(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render(
                'game/market/disabled.html.twig',
                [
                    'player' => $player
                ]
            );
        }

        $marketItems = $this->marketItemRepository->findByWorldMarketItemType(
            $player->getWorld(),
            MarketItem::TYPE_BUY
        );

        return $this->render(
            'game/market/sell.html.twig',
            [
                'player' => $player,
                'marketItems' => $marketItems
            ]
        );
    }

    public function sellOrder(int $marketItemId): RedirectResponse
    {
        try {
            $this->marketActionService->sellOrder($this->getPlayer(), $marketItemId);
            $this->addFlash('success', 'You sold something on the market!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Market/Sell');
    }

    public function listOrders(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render(
                'game/market/disabled.html.twig',
                [
                    'player' => $player
                ]
            );
        }

        return $this->render(
            'game/market/listOrders.html.twig',
            [
                'player' => $player,
                'marketItems' => $player->getMarketItems()
            ]
        );
    }

    public function cancelOrder(int $marketItemId): RedirectResponse
    {
        try {
            $this->marketActionService->cancelOrder($this->getPlayer(), $marketItemId);
            $this->addFlash('success', 'You cancelled your order!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Market/ListOrders');
    }

    public function createOrder(Request $request): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render(
                'game/market/disabled.html.twig',
                [
                    'player' => $player
                ]
            );
        }

        $form = $this->createForm(MarketOrderType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->marketActionService->createOrder(
                    $player,
                    $form->getData()['resource'],
                    $form->getData()['price'],
                    $form->getData()['amount'],
                    $form->getData()['option'],
                );
                $this->addFlash('success', 'Created new market order');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'game/market/createOrder.html.twig',
            [
                'player' => $player,
                'form' => $form->createView(),
                'gameResources' => GameResource::getAll()
            ]
        );
    }
}
