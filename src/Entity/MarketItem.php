<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use InvalidArgumentException;

class MarketItem
{
    /**
     * @var string
     */
    public const TYPE_BUY = 'buy';

    /**
     * @var string
     */
    public const TYPE_SELL = 'sell';

    private ?int $id;
    private int $amount;
    private int $price;
    private World $world;
    private Player $player;
    private string $gameResource;
    private string $type;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getGameResource(): string
    {
        return $this->gameResource;
    }

    public function setGameResource(string $gameResource): void
    {
        $this->gameResource = $gameResource;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_BUY, self::TYPE_SELL], true)) {
            throw new InvalidArgumentException("Invalid type");
        }

        $this->type = $type;
    }

    public static function createForPlayer(
        Player $player,
        string $gameResource,
        int $amount,
        int $price,
        string $type
    ): MarketItem {
        $marketItem = new MarketItem();
        $marketItem->setWorld($player->getWorld());
        $marketItem->setPlayer($player);
        $marketItem->setGameResource($gameResource);
        $marketItem->setAmount($amount);
        $marketItem->setPrice($price);
        $marketItem->setType($type);

        return $marketItem;
    }
}
