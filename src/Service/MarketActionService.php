<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

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

        if ($marketItem->getPrice() > $player->getCash()) {
            throw new RunTimeException('You do not have enough cash!');
        }

        $player->setCash($player->getCash() - $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayer->setCash($marketItemPlayer->getCash() + $marketItem->getPrice());

        // XXX TODO: Fix better
        $gameResource = $marketItem->getGameResource();
        switch ($gameResource->getName()) {
            case 'Wood':
                $player->setWood($player->getWood() + $marketItem->getAmount());
                break;
            case 'Food':
                $player->setFood($player->getFood() + $marketItem->getAmount());
                break;
            case 'Steel':
                $player->setSteel($player->getSteel() + $marketItem->getAmount());
                break;
            default:
                throw new RunTimeException('Unknown resource type!');
        }

        // Set update news feed
        $marketItemPlayer->setMarket(true);

        $this->playerRepository->save($player);
        $this->playerRepository->save($marketItemPlayer);
        $this->marketItemRepository->remove($marketItem);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $buyReport = Report::create($player, time(), 4, $buyMessage);
        $this->reportRepository->save($buyReport);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $sellReport = Report::create($marketItemPlayer, time(), 4, $sellMessage);
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

        if ($marketItem->getType() == MarketItem::TYPE_BUY) {
            $player->setCash($player->getCash() + $marketItem->getPrice());
        } else {
            // XXX TODO: Fix better
            $gameResource = $marketItem->getGameResource();
            switch ($gameResource->getName()) {
                case 'Wood':
                    $player->setWood($player->getWood() + $marketItem->getAmount());
                    break;
                case 'Food':
                    $player->setFood($player->getFood() + $marketItem->getAmount());
                    break;
                case 'Steel':
                    $player->setSteel($player->getSteel() + $marketItem->getAmount());
                    break;
                default:
                    throw new RunTimeException('Unknown resource type!');
            }
        }

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

        $player->setCash($player->getCash() + $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayer->setCash($marketItemPlayer->getCash() - $marketItem->getPrice());

        // XXX TODO: Fix better
        $gameResource = $marketItem->getGameResource();
        switch ($gameResource->getName()) {
            case 'Wood':
                $player->setWood($player->getWood() - $marketItem->getAmount());
                break;
            case 'Food':
                $player->setFood($player->getFood() - $marketItem->getAmount());
                break;
            case 'Steel':
                $player->setSteel($player->getSteel() - $marketItem->getAmount());
                break;
            default:
                throw new RunTimeException('Unknown resource type!');
        }

        // Set update news feed
        $marketItemPlayer->setMarket(true);

        $this->playerRepository->save($player);
        $this->playerRepository->save($marketItemPlayer);
        $this->marketItemRepository->remove($marketItem);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $sellReport = Report::create($player, time(), 4, $sellMessage);
        $this->reportRepository->save($sellReport);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()->getName()}.";
        $buyReport = Report::create($marketItemPlayer, time(), 4, $buyMessage);
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

        if ($action == MarketItem::TYPE_BUY) {
            if ($price > $player->getCash()) {
                throw new RunTimeException("You do not have enough cash!");
            }

            $player->setCash($player->getCash() - $price);
        } elseif ($action == MarketItem::TYPE_SELL) {
            switch ($gameResource->getName()) {
                case 'Wood':
                    if ($amount > $player->getWood()) {
                        throw new RunTimeException("You do not have enough wood!");
                    }

                    $player->setWood($player->getWood() - $amount);
                    break;
                case 'Food':
                    if ($amount > $player->getFood()) {
                        throw new RunTimeException("You do not have enough food!");
                    }
                    $player->setFood($player->getFood() - $amount);
                    break;
                case 'Steel':
                    if ($amount > $player->getSteel()) {
                        throw new RunTimeException("You do not have enough steel!");
                    }
                    $player->setSteel($player->getSteel() - $amount);
                    break;
                default:
                    throw new RunTimeException("Unknown resource type!");
            }
        } else {
            throw new RunTimeException("Invalid option!");
        }

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
