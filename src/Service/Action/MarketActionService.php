<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\GameResourceRepository;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use RuntimeException;

final class MarketActionService
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
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * MarketActionService constructor.
     *
     * @param GameResourceRepository $gameResourceRepository
     * @param MarketItemRepository $marketItemRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        GameResourceRepository $gameResourceRepository,
        MarketItemRepository $marketItemRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository
    ) {
        $this->gameResourceRepository = $gameResourceRepository;
        $this->marketItemRepository = $marketItemRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param Player $player
     * @param int $marketItemId
     */
    public function buyOrder(Player $player, int $marketItemId): void
    {
        $this->ensureMarketEnabled($player);

        $marketItem = $this->getMarketItem($player, $marketItemId);

        if ($marketItem->getType() != MarketItem::TYPE_SELL) {
            throw new RunTimeException('Market order is not a buy order!');
        }

        if ($marketItem->getPlayer()->getId() == $player->getId()) {
            throw new RunTimeException('Can not buy your own!');
        }

        $resources = $player->getResources();
        if ($marketItem->getPrice() > $resources->getCash()) {
            throw new RunTimeException('You do not have enough cash!');
        }

        $resources->setCash($resources->getCash() - $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayerResources = $marketItemPlayer->getResources();
        $marketItemPlayerResources->setCash($marketItemPlayerResources->getCash() + $marketItem->getPrice());

        // XXX TODO: Fix better
        $gameResource = $marketItem->getGameResource();
        switch ($gameResource->getName()) {
            case 'Wood':
                $resources->setWood($resources->getWood() + $marketItem->getAmount());
                break;
            case 'Food':
                $resources->setFood($resources->getFood() + $marketItem->getAmount());
                break;
            case 'Steel':
                $resources->setSteel($resources->getSteel() + $marketItem->getAmount());
                break;
            default:
                throw new RunTimeException('Unknown resource type!');
        }

        // Set update news feed
        $marketItemPlayerNotifications = $marketItemPlayer->getNotifications();
        $marketItemPlayerNotifications->setMarket(true);

        $player->setResources($resources);
        $marketItemPlayer->setResources($marketItemPlayerResources);
        $marketItemPlayer->setNotifications($marketItemPlayerNotifications);

        $this->playerRepository->save($player);
        $this->playerRepository->save($marketItemPlayer);
        $this->marketItemRepository->remove($marketItem);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $buyReport = Report::createForPlayer($player, time(), Report::TYPE_MARKET, $buyMessage);
        $this->reportRepository->save($buyReport);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $sellReport = Report::createForPlayer($marketItemPlayer, time(), Report::TYPE_MARKET, $sellMessage);
        $this->reportRepository->save($sellReport);
    }

    /**
     * @param Player $player
     * @param int $marketItemId
     */
    public function cancelOrder(Player $player, int $marketItemId): void
    {
        $this->ensureMarketEnabled($player);

        $marketItem = $this->getMarketItem($player, $marketItemId);

        if ($marketItem->getPlayer()->getId() != $player->getId()) {
            throw new RunTimeException('You can not cancel orders that do not belong to you!\'');
        }

        $resources = $player->getResources();
        if ($marketItem->getType() == MarketItem::TYPE_BUY) {
            $resources->setCash($resources->getCash() + $marketItem->getPrice());
        } else {
            // XXX TODO: Fix better
            $gameResource = $marketItem->getGameResource();
            switch ($gameResource->getName()) {
                case 'Wood':
                    $resources->setWood($resources->getWood() + $marketItem->getAmount());
                    break;
                case 'Food':
                    $resources->setFood($resources->getFood() + $marketItem->getAmount());
                    break;
                case 'Steel':
                    $resources->setSteel($resources->getSteel() + $marketItem->getAmount());
                    break;
                default:
                    throw new RunTimeException('Unknown resource type!');
            }
        }

        $player->setResources($resources);
        $this->playerRepository->save($player);
        $this->marketItemRepository->remove($marketItem);
    }

    /**
     * @param Player $player
     * @param int $marketItemId
     */
    public function sellOrder(Player $player, int $marketItemId): void
    {
        $this->ensureMarketEnabled($player);

        $marketItem = $this->getMarketItem($player, $marketItemId);

        if ($marketItem->getType() != MarketItem::TYPE_BUY) {
            throw new RunTimeException('Market order is not a sell order!');
        }

        if ($marketItem->getPlayer()->getId() == $player->getId()) {
            throw new RunTimeException('Can not sell to yourself!');
        }

        $resources = $player->getResources();
        $resources->setCash($resources->getCash() + $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayerResources = $marketItemPlayer->getResources();
        $marketItemPlayerResources->setCash($marketItemPlayerResources->getCash() - $marketItem->getPrice());

        // XXX TODO: Fix better
        $gameResource = $marketItem->getGameResource();
        switch ($gameResource->getName()) {
            case 'Wood':
                $resources->setWood($resources->getWood() - $marketItem->getAmount());
                break;
            case 'Food':
                $resources->setFood($resources->getFood() - $marketItem->getAmount());
                break;
            case 'Steel':
                $resources->setSteel($resources->getSteel() - $marketItem->getAmount());
                break;
            default:
                throw new RunTimeException('Unknown resource type!');
        }

        // Set update news feed
        $marketItemPlayerNotifications = $marketItemPlayer->getNotifications();
        $marketItemPlayerNotifications->setMarket(true);

        $player->setResources($resources);
        $marketItemPlayer->setResources($marketItemPlayerResources);
        $marketItemPlayer->setNotifications($marketItemPlayerNotifications);
        $this->playerRepository->save($player);
        $this->playerRepository->save($marketItemPlayer);
        $this->marketItemRepository->remove($marketItem);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $sellReport = Report::createForPlayer($player, time(), Report::TYPE_MARKET, $sellMessage);
        $this->reportRepository->save($sellReport);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $buyReport = Report::createForPlayer($marketItemPlayer, time(), Report::TYPE_MARKET, $buyMessage);
        $this->reportRepository->save($buyReport);
    }

    /**
     * @param Player $player
     * @param int $gameResourceId
     * @param int $price
     * @param int $amount
     * @param string $action
     */
    public function placeOffer(Player $player, int $gameResourceId, int $price, int $amount, string $action): void
    {
        if ($price < 1 || $amount < 1) {
            throw new RunTimeException("Invalid input!");
        }

        $gameResource = $this->gameResourceRepository->find($gameResourceId);
        if (!$gameResource) {
            throw new RunTimeException("Invalid resource!");
        }

        $resources = $player->getResources();


        if ($action == MarketItem::TYPE_BUY) {
            if ($price > $resources->getCash()) {
                throw new RunTimeException("You do not have enough cash!");
            }

            $resources->setCash($resources->getCash() - $price);
        } elseif ($action == MarketItem::TYPE_SELL) {
            switch ($gameResource->getName()) {
                case 'Wood':
                    if ($amount > $resources->getWood()) {
                        throw new RunTimeException("You do not have enough wood!");
                    }

                    $resources->setWood($resources->getWood() - $amount);
                    break;
                case 'Food':
                    if ($amount > $resources->getFood()) {
                        throw new RunTimeException("You do not have enough food!");
                    }
                    $resources->setFood($resources->getFood() - $amount);
                    break;
                case 'Steel':
                    if ($amount > $resources->getSteel()) {
                        throw new RunTimeException("You do not have enough steel!");
                    }
                    $resources->setSteel($resources->getSteel() - $amount);
                    break;
                default:
                    throw new RunTimeException("Unknown resource type!");
            }
        } else {
            throw new RunTimeException("Invalid option!");
        }

        $player->setResources($resources);
        $marketItem = MarketItem::createForPlayer($player, $gameResource, $amount, $price, $action);
        $this->marketItemRepository->save($marketItem);
        $this->playerRepository->save($player);
    }

    /**
     * @param Player $player
     */
    private function ensureMarketEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            throw new RunTimeException("Market not enabled!");
        }
    }

    /**
     * @param Player $player
     * @param int $marketItemId
     * @return MarketItem
     */
    private function getMarketItem(Player $player, int $marketItemId): MarketItem
    {
        $marketItem = $this->marketItemRepository->find($marketItemId);

        if (!$marketItem) {
            throw new RunTimeException('Market order does not exist!');
        }

        if ($marketItem->getWorld()->getId() != $player->getWorld()->getId()) {
            throw new RunTimeException('Wrong game world!');
        }

        return $marketItem;
    }
}
