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
    private MarketItemRepository $marketItemRepository;
    private PlayerRepository $playerRepository;
    private ReportRepository $reportRepository;

    public function __construct(
        MarketItemRepository $marketItemRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository
    ) {
        $this->marketItemRepository = $marketItemRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
    }

    public function buyOrder(Player $player, int $marketItemId): void
    {
        $this->ensureMarketEnabled($player);

        $marketItem = $this->getMarketItem($player, $marketItemId);

        if ($marketItem->getType() != MarketItem::TYPE_SELL) {
            throw new RuntimeException('Market order is not a buy order!');
        }

        $this->ensureMarketItemNotOwnedByPlayer($marketItem, $player);

        $resources = $player->getResources();
        if ($marketItem->getPrice() > $resources->getCash()) {
            throw new RuntimeException('You do not have enough cash!');
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

    private function createBuyReports(MarketItem $marketItem, Player $player): void
    {
        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($player, $buyMessage);

        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($marketItem->getPlayer(), $sellMessage);
    }

    public function cancelOrder(Player $player, int $marketItemId): void
    {
        $this->ensureMarketEnabled($player);

        $marketItem = $this->getMarketItem($player, $marketItemId);

        if ($marketItem->getPlayer()->getId() != $player->getId()) {
            throw new RuntimeException('You can not cancel orders that do not belong to you!\'');
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

    public function sellOrder(Player $player, int $marketItemId): void
    {
        $this->ensureMarketEnabled($player);

        $marketItem = $this->getMarketItem($player, $marketItemId);

        if ($marketItem->getType() != MarketItem::TYPE_BUY) {
            throw new RuntimeException('Market order is not a sell order!');
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

    private function createSellReports(MarketItem $marketItem, Player $player): void
    {
        $sellMessage = "You sold {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($player, $sellMessage);

        $buyMessage = "You bought {$marketItem->getAmount()} {$marketItem->getGameResource()}.";
        $this->createReport($marketItem->getPlayer(), $buyMessage);
    }

    public function placeOffer(Player $player, string $gameResource, int $price, int $amount, string $action): void
    {
        $this->ensureValidGameResource($gameResource);

        if ($price < 1 || $amount < 1) {
            throw new RuntimeException("Invalid input!");
        }

        $resources = $player->getResources();

        if ($action == MarketItem::TYPE_BUY) {
            if ($price > $resources->getCash()) {
                throw new RuntimeException("You do not have enough cash!");
            }

            $resources->setCash($resources->getCash() - $price);
        } elseif ($action == MarketItem::TYPE_SELL) {
            $resources = $this->substractAndValidateGameResources($gameResource, $resources, $amount);
        } else {
            throw new RuntimeException("Invalid option!");
        }

        $player->setResources($resources);
        $marketItem = MarketItem::createForPlayer($player, $gameResource, $amount, $price, $action);
        $this->marketItemRepository->save($marketItem);
        $this->playerRepository->save($player);
    }

    private function ensureMarketEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getMarket()) {
            throw new RuntimeException("Market not enabled!");
        }
    }

    private function ensureMarketItemNotOwnedByPlayer(MarketItem $marketItem, Player $player): void
    {
        if ($marketItem->getPlayer()->getId() === $player->getId()) {
            throw new RuntimeException('Can not buy or sell to yourself!');
        }
    }

    private function ensureValidGameResource(string $gameResource): void
    {
        if (!GameResource::isValid($gameResource)) {
            throw new RuntimeException("Invalid resource!");
        }
    }

    private function getMarketItem(Player $player, int $marketItemId): MarketItem
    {
        $marketItem = $this->marketItemRepository->find($marketItemId);

        if ($marketItem === null) {
            throw new RuntimeException('Market order does not exist!');
        }

        if ($marketItem->getWorld()->getId() != $player->getWorld()->getId()) {
            throw new RuntimeException('Wrong game world!');
        }

        return $marketItem;
    }

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
                throw new RuntimeException('Unknown resource type!');
        }

        return $resources;
    }

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
                throw new RuntimeException('Unknown resource type!');
        }

        return $resources;
    }

    private function substractAndValidateGameResources(
        string $gameResource,
        Resources $resources,
        int $amount
    ): Resources {
        switch ($gameResource) {
            case GameResource::GAME_RESOURCE_WOOD:
                $this->ensureEnoughResources($amount, $resources->getWood(), GameResource::GAME_RESOURCE_WOOD);
                $resources->setWood($resources->getWood() - $amount);
                break;
            case GameResource::GAME_RESOURCE_FOOD:
                $this->ensureEnoughResources($amount, $resources->getFood(), GameResource::GAME_RESOURCE_FOOD);
                $resources->setFood($resources->getFood() - $amount);
                break;
            case GameResource::GAME_RESOURCE_STEEL:
                $this->ensureEnoughResources($amount, $resources->getSteel(), GameResource::GAME_RESOURCE_STEEL);
                $resources->setSteel($resources->getSteel() - $amount);
                break;
            default:
                throw new RuntimeException("Unknown resource type!");
        }

        return $resources;
    }

    private function ensureEnoughResources(int $amount, int $resourceAmount, string $resourceName): void
    {
        if ($amount > $resourceAmount) {
            throw new RuntimeException("You do not have enough {$resourceName}!");
        }
    }

    private function createReport(Player $player, string $text): void
    {
        $report = Report::createForPlayer($player, time(), Report::TYPE_MARKET, $text);
        $this->reportRepository->save($report);
    }
}
