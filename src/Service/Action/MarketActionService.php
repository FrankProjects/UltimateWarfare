<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\MarketItemRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use RuntimeException;

final class MarketActionService
{
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
     * @param MarketItemRepository $marketItemRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        MarketItemRepository $marketItemRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository
    ) {
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

        $this->ensureMarketItemNotOwnedByPlayer($marketItem, $player);

        $resources = $player->getResources();
        if ($marketItem->getPrice() > $resources->getCash()) {
            throw new RunTimeException('You do not have enough cash!');
        }

        $resources->setCash($resources->getCash() - $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayerResources = $marketItemPlayer->getResources();
        $marketItemPlayerResources->setCash($marketItemPlayerResources->getCash() + $marketItem->getPrice());

        $resources = $this->addGameResources($marketItem, $resources);

        $marketItemPlayerNotifications = $marketItemPlayer->getNotifications();
        $marketItemPlayerNotifications->setMarket(true);

        $player->setResources($resources);
        $marketItemPlayer->setResources($marketItemPlayerResources);
        $marketItemPlayer->setNotifications($marketItemPlayerNotifications);

        $this->playerRepository->save($player);
        $this->playerRepository->save($marketItemPlayer);
        $this->marketItemRepository->remove($marketItem);

        $this->createBuyReports($marketItem, $player);
    }

    /**
     * @param MarketItem $marketItem
     * @param Player $player
     */
    private function createBuyReports(MarketItem $marketItem, Player $player): void
    {
        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($player, $buyMessage);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($marketItem->getPlayer(), $sellMessage);
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
            $resources = $this->addGameResources($marketItem, $resources);
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

        $this->ensureMarketItemNotOwnedByPlayer($marketItem, $player);

        $resources = $player->getResources();
        $resources->setCash($resources->getCash() + $marketItem->getPrice());
        $marketItemPlayer = $marketItem->getPlayer();
        $marketItemPlayerResources = $marketItemPlayer->getResources();
        $marketItemPlayerResources->setCash($marketItemPlayerResources->getCash() - $marketItem->getPrice());

        $resources = $this->substractGameResources($marketItem, $resources);

        $marketItemPlayerNotifications = $marketItemPlayer->getNotifications();
        $marketItemPlayerNotifications->setMarket(true);

        $player->setResources($resources);
        $marketItemPlayer->setResources($marketItemPlayerResources);
        $marketItemPlayer->setNotifications($marketItemPlayerNotifications);
        $this->playerRepository->save($player);
        $this->playerRepository->save($marketItemPlayer);
        $this->marketItemRepository->remove($marketItem);

        $this->createSellReports($marketItem, $player);
    }

    /**
     * @param MarketItem $marketItem
     * @param Player $player
     */
    private function createSellReports(MarketItem $marketItem, Player $player): void
    {
        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($player, $sellMessage);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($marketItem->getPlayer(), $buyMessage);
    }

    /**
     * @param Player $player
     * @param string $gameResource
     * @param int $price
     * @param int $amount
     * @param string $action
     */
    public function placeOffer(Player $player, string $gameResource, int $price, int $amount, string $action): void
    {
        if ($price < 1 || $amount < 1) {
            throw new RunTimeException("Invalid input!");
        }

        if (!GameResource::isValid($gameResource)) {
            throw new RunTimeException("Invalid resource!");
        }

        $resources = $player->getResources();

        if ($action == MarketItem::TYPE_BUY) {
            if ($price > $resources->getCash()) {
                throw new RunTimeException("You do not have enough cash!");
            }

            $resources->setCash($resources->getCash() - $price);
        } elseif ($action == MarketItem::TYPE_SELL) {
            $resources = $this->substractAndValidateGameResources($gameResource, $resources, $amount);
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
     * @param MarketItem $marketItem
     * @param Player $player
     */
    private function ensureMarketItemNotOwnedByPlayer(MarketItem $marketItem, Player $player): void
    {
        if ($marketItem->getPlayer()->getId() === $player->getId()) {
            throw new RunTimeException('Can not buy or sell to yourself!');
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

    /**
     * @param MarketItem $marketItem
     * @param Resources $resources
     * @return Resources
     */
    private function addGameResources(MarketItem $marketItem, Resources $resources): Resources
    {
        switch ($marketItem->getGameResource()) {
            case GameResource::GAME_RESOURCE_WOOD:
                $resources->setWood($resources->getWood() + $marketItem->getAmount());
                break;
            case GameResource::GAME_RESOURCE_FOOD:
                $resources->setFood($resources->getFood() + $marketItem->getAmount());
                break;
            case GameResource::GAME_RESOURCE_STEEL:
                $resources->setSteel($resources->getSteel() + $marketItem->getAmount());
                break;
            default:
                throw new RunTimeException('Unknown resource type!');
        }

        return $resources;
    }


    /**
     * @param MarketItem $marketItem
     * @param Resources $resources
     * @return Resources
     */
    private function substractGameResources(MarketItem $marketItem, Resources $resources): Resources
    {
        switch ($marketItem->getGameResource()) {
            case GameResource::GAME_RESOURCE_WOOD:
                $resources->setWood($resources->getWood() - $marketItem->getAmount());
                break;
            case GameResource::GAME_RESOURCE_FOOD:
                $resources->setFood($resources->getFood() - $marketItem->getAmount());
                break;
            case GameResource::GAME_RESOURCE_STEEL:
                $resources->setSteel($resources->getSteel() - $marketItem->getAmount());
                break;
            default:
                throw new RunTimeException('Unknown resource type!');
        }

        return $resources;
    }

    /**
     * @param string $gameResource
     * @param Resources $resources
     * @param int $amount
     * @return Resources
     */
    private function substractAndValidateGameResources(string $gameResource, Resources $resources, int $amount): Resources
    {
        switch ($gameResource) {
            case GameResource::GAME_RESOURCE_WOOD:
                if ($amount > $resources->getWood()) {
                    throw new RunTimeException("You do not have enough wood!");
                }
                $resources->setWood($resources->getWood() - $amount);
                break;
            case GameResource::GAME_RESOURCE_FOOD:
                if ($amount > $resources->getFood()) {
                    throw new RunTimeException("You do not have enough food!");
                }
                $resources->setFood($resources->getFood() - $amount);
                break;
            case GameResource::GAME_RESOURCE_STEEL:
                if ($amount > $resources->getSteel()) {
                    throw new RunTimeException("You do not have enough steel!");
                }
                $resources->setSteel($resources->getSteel() - $amount);
                break;
            default:
                throw new RunTimeException("Unknown resource type!");
        }

        return $resources;
    }

    /**
     * @param Player $player
     * @param string $text
     */
    private function createReport(Player $player, string $text): void
    {
        $report = Report::createForPlayer($player, time(), Report::TYPE_MARKET, $text);
        $this->reportRepository->save($report);
    }
}
