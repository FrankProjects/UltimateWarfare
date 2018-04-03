<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\MarketItemType;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render('game/market/disabled.html.twig', [
                'player' => $player
            ]);
        }

        /** @var MarketItemType $marketItemType */
        $marketItemType = $this->getEm()->getRepository('Game:MarketItemType')
            ->findOneBy(['name' => MarketItemType::TYPE_NAME_SELL]);

        /** @var MarketItemRepository $marketItemRepository */
        $marketItemRepository = $this->getEm()->getRepository('Game:MarketItem');
        $marketItems = $marketItemRepository->findByWorldMarketItemType($player->getWorld(), $marketItemType);

        return $this->render('game/market/buy.html.twig', [
            'player' => $player,
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param Request $request
     * @param int $marketItemId
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function buyOrder(Request $request, int $marketItemId): RedirectResponse
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        /** @var MarketItemRepository $marketItemRepository */
        $marketItemRepository = $this->getEm()->getRepository('Game:MarketItem');

        /** @var MarketItem $marketItem */
        $marketItem = $marketItemRepository->find($marketItemId);

        if (!$marketItem) {
            $this->addFlash('error', 'Market order does not exist!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getMarketItemType()->getName() != MarketItemType::TYPE_NAME_BUY) {
            $this->addFlash('error', 'Market order is not a buy order!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getWorld()->getId() != $world->getId()) {
            $this->addFlash('error', 'Wrong game world!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getPlayer()->getId() == $player->getId()) {
            $this->addFlash('error', 'Can not buy your own!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getPrice() > $player->getCash()) {
            $this->addFlash('error', 'You do not have enough cash!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        $player->setCash($player->getCash() - $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayer->setCash($marketItemPlayer->getCash() + $marketItem->getPrice());

        $gameResource = $marketItem->getGameResource();
        switch ($gameResource->getName()) {
            case 'wood':
                $player->setWood($player->getWood() + $marketItem->getAmount());
                break;
            case 'food':
                $player->setFood($player->getFood() + $marketItem->getAmount());
                break;
            case 'steel':
                $player->setSteel($player->getSteel() + $marketItem->getAmount());
                break;
            default:
                $this->addFlash('error', 'Unknown resource type!');
                return $this->redirect($this->generateUrl('Game/Market'));
        }

        // Set update news feed
        $marketItemPlayer->setMarket(true);

        $this->getEm()->persist($player);
        $this->getEm()->persist($marketItemPlayer);
        $this->getEm()->remove($marketItem);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $buyReport = Report::create($player, time(), 4, $buyMessage);
        $this->getEm()->persist($buyReport);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $sellReport = Report::create($marketItemPlayer, time(), 4, $sellMessage);
        $this->getEm()->persist($sellReport);

        $this->getEm()->flush();

        $this->addFlash('success', $buyMessage);

        return $this->redirect($this->generateUrl('Game/Market'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function sell(Request $request): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->render('game/market/disabled.html.twig', [
                'player' => $player
            ]);
        }

        /** @var MarketItemType $marketItemType */
        $marketItemType = $this->getEm()->getRepository('Game:MarketItemType')
            ->findOneBy(['name' => MarketItemType::TYPE_NAME_BUY]);

        /** @var MarketItemRepository $marketItemRepository */
        $marketItemRepository = $this->getEm()->getRepository('Game:MarketItem');
        $marketItems = $marketItemRepository->findByWorldMarketItemType($player->getWorld(), $marketItemType);

        return $this->render('game/market/sell.html.twig', [
            'player' => $player,
            'marketItems' => $marketItems
        ]);
    }

    /**
     * @param Request $request
     * @param int $marketItemId
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sellOrder(Request $request, int $marketItemId): RedirectResponse
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        /** @var MarketItemRepository $marketItemRepository */
        $marketItemRepository = $this->getEm()->getRepository('Game:MarketItem');

        /** @var MarketItem $marketItem */
        $marketItem = $marketItemRepository->find($marketItemId);

        if (!$marketItem) {
            $this->addFlash('error', 'Market order does not exist!');
            return $this->redirect($this->generateUrl('Game/Market/Sell'));
        }

        if ($marketItem->getMarketItemType()->getName() != MarketItemType::TYPE_NAME_SELL) {
            $this->addFlash('error', 'Market order is not a sell order!');
            return $this->redirect($this->generateUrl('Game/Market/Sell'));
        }

        if ($marketItem->getWorld()->getId() != $world->getId()) {
            $this->addFlash('error', 'Wrong game world!');
            return $this->redirect($this->generateUrl('Game/Market/Sell'));
        }

        if ($marketItem->getPlayer()->getId() == $player->getId()) {
            $this->addFlash('error', 'Can not sell to yourself!');
            return $this->redirect($this->generateUrl('Game/Market/Sell'));
        }

        $player->setCash($player->getCash() + $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayer->setCash($marketItemPlayer->getCash() - $marketItem->getPrice());

        $gameResource = $marketItem->getGameResource();
        switch ($gameResource->getName()) {
            case 'wood':
                $player->setWood($player->getWood() - $marketItem->getAmount());
                break;
            case 'food':
                $player->setFood($player->getFood() - $marketItem->getAmount());
                break;
            case 'steel':
                $player->setSteel($player->getSteel() - $marketItem->getAmount());
                break;
            default:
                $this->addFlash('error', 'Unknown resource type!');
                return $this->redirect($this->generateUrl('Game/Market/Sell'));
        }

        // Set update news feed
        $marketItemPlayer->setMarket(true);

        $this->getEm()->persist($player);
        $this->getEm()->persist($marketItemPlayer);
        $this->getEm()->remove($marketItem);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $sellReport = Report::create($player, time(), 4, $sellMessage);
        $this->getEm()->persist($sellReport);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $buyReport = Report::create($marketItemPlayer, time(), 4, $buyMessage);
        $this->getEm()->persist($buyReport);

        $this->getEm()->flush();

        $this->addFlash('success', $sellMessage);

        return $this->redirect($this->generateUrl('Game/Market/Sell'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function offers(Request $request): Response
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
     * @param Request $request
     * @param int $marketItemId
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cancelOrder(Request $request, int $marketItemId): RedirectResponse
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        /** @var MarketItemRepository $marketItemRepository */
        $marketItemRepository = $this->getEm()->getRepository('Game:MarketItem');

        /** @var MarketItem $marketItem */
        $marketItem = $marketItemRepository->find($marketItemId);

        if (!$marketItem) {
            $this->addFlash('error', 'Market order does not exist!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getWorld()->getId() != $world->getId()) {
            $this->addFlash('error', 'Wrong game world!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getPlayer()->getId() != $player->getId()) {
            $this->addFlash('error', 'You can not cancel orders that do not belong to you!');
            return $this->redirect($this->generateUrl('Game/Market'));
        }

        if ($marketItem->getMarketItemType()->getName() == MarketItemType::TYPE_NAME_BUY) {
            $player->setCash($player->getCash() + $marketItem->getPrice());
        } else {
            $gameResource = $marketItem->getGameResource();
            switch ($gameResource->getName()) {
                case 'wood':
                    $player->setWood($player->getWood() + $marketItem->getAmount());
                    break;
                case 'food':
                    $player->setFood($player->getFood() + $marketItem->getAmount());
                    break;
                case 'steel':
                    $player->setSteel($player->getSteel() + $marketItem->getAmount());
                    break;
                default:
                    $this->addFlash('error', 'Unknown resource type!');
                    return $this->redirect($this->generateUrl('Game/Market'));
            }
        }

        $this->getEm()->persist($player);
        $this->getEm()->remove($marketItem);
        $this->getEm()->flush();

        $this->addFlash('success', 'You cancelled your order!');

        return $this->redirect($this->generateUrl('Game/Market'));
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

        return $this->render('game/market/placeOffer.html.twig', [
            'player' => $player,
            'marketItems' => []
        ]);
    }
}
