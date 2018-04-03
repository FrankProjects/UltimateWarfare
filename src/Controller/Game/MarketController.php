<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\MarketItemType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MarketController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function buy(Request $request): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $marketItemType = $em->getRepository('Game:MarketItemType')
            -> findOneBy(['name' => MarketItemType::TYPE_NAME_SELL]);

        $marketItems = $em->getRepository('Game:MarketItem')
            ->findByWorldMarketItemType($player->getWorld(), $marketItemType);

        return $this->render('game/market/buy.html.twig', [
            'player' => $this->getPlayer(),
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function sell(Request $request): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $marketItemType = $em->getRepository('Game:MarketItemType')
            -> findOneBy(['name' => MarketItemType::TYPE_NAME_BUY]);

        $marketItems = $em->getRepository('Game:MarketItem')
            ->findByWorldMarketItemType($player->getWorld(), $marketItemType);

        return $this->render('game/market/sell.html.twig', [
            'player' => $this->getPlayer(),
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function offers(Request $request): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();

        $marketItems = $em->getRepository('Game:MarketItem')
            ->findBy(['player' => $player]);

        return $this->render('game/market/offers.html.twig', [
            'player' => $this->getPlayer(),
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function placeOffer(Request $request): Response
    {
        return $this->render('game/market/placeOffer.html.twig', [
            'player' => $this->getPlayer(),
            'marketItems' => []
        ]);
    }
}
