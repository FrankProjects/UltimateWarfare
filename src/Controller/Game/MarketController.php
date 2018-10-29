<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Repository\GameResourceRepository;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use FrankProjects\UltimateWarfare\Service\Action\MarketActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MarketController extends BaseGameController
{
    /**
     * @var GameResourceRepository
     */
    private $gameResourceRepository;

    /**
     * @var MarketItemRepository
     */
    private $marketItemRepository;

    /**
     * @var MarketActionService
     */
    private $marketActionService;

    /**
     * MarketController constructor.
     *
     * @param GameResourceRepository $gameResourceRepository
     * @param MarketItemRepository $marketItemRepository
     * @param MarketActionService $marketActionService
     */
    public function __construct(
        GameResourceRepository $gameResourceRepository,
        MarketItemRepository $marketItemRepository,
        MarketActionService $marketActionService
    ) {
        $this->gameResourceRepository = $gameResourceRepository;
        $this->marketItemRepository = $marketItemRepository;
        $this->marketActionService = $marketActionService;
    }

    /**
     * @return Response
     */
    public function buy(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render('game/market/disabled.html.twig', [
                'player' => $player
            ]);
        }

        $marketItems = $this->marketItemRepository->findByWorldMarketItemType($player->getWorld(), MarketItem::TYPE_SELL);

        return $this->render('game/market/buy.html.twig', [
            'player' => $player,
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param int $marketItemId
     * @return RedirectResponse
     */
    public function buyOrder(int $marketItemId): RedirectResponse
    {
        try {
            $this->marketActionService->buyOrder($this->getPlayer(), $marketItemId);
            $this->addFlash('success', 'You bought something on the market!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('Game/Market'));
    }

    /**
     * @return Response
     */
    public function sell(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render('game/market/disabled.html.twig', [
                'player' => $player
            ]);
        }

        $marketItems = $this->marketItemRepository->findByWorldMarketItemType($player->getWorld(), MarketItem::TYPE_BUY);

        return $this->render('game/market/sell.html.twig', [
            'player' => $player,
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param int $marketItemId
     * @return RedirectResponse
     */
    public function sellOrder(int $marketItemId): RedirectResponse
    {
        try {
            $this->marketActionService->sellOrder($this->getPlayer(), $marketItemId);
            $this->addFlash('success', 'You sold something on the market!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('Game/Market/Sell'));
    }

    /**
     * @return Response
     */
    public function offers(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render('game/market/disabled.html.twig', [
                'player' => $player
            ]);
        }

        return $this->render('game/market/offers.html.twig', [
            'player' => $player,
            'marketItems' => $player->getMarketItems()
        ]);
    }

    /**
     * @param int $marketItemId
     * @return RedirectResponse
     */
    public function cancelOrder(int $marketItemId): RedirectResponse
    {
        try {
            $this->marketActionService->cancelOrder($this->getPlayer(), $marketItemId);
            $this->addFlash('success', 'You cancelled your order!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('Game/Market/Offers'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function placeOffer(Request $request): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render('game/market/disabled.html.twig', [
                'player' => $player
            ]);
        }

        $gameResources = $this->gameResourceRepository->findAll();

        if ($request->getMethod() == 'POST') {
            $price = intval($request->get('price'));
            $amount = intval($request->get('amount'));
            $resource = intval($request->get('resource'));
            $option = $request->get('option');

            try {
                $this->marketActionService->placeOffer($player, $resource, $price, $amount, $option);
                $this->addFlash('success', 'you placed a new offer on the market!');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('game/market/placeOffer.html.twig', [
            'player' => $player,
            'gameResources' => $gameResources
        ]);
    }
}
