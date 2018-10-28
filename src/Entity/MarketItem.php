<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * MarketItem
 */
class MarketItem
{
    /**
     * @var string
     */
    const TYPE_BUY = 'buy';

    /**
     * @var string
     */
    const TYPE_SELL = 'sell';

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var int
     */
    private $price;

    /**
     * @var World
     */
    private $world;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var GameResource
     */
    private $gameResource;

    /**
     * @var string
     */
    private $type;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set price
     *
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @param World $world
     */
    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return GameResource
     */
    public function getGameResource(): GameResource
    {
        return $this->gameResource;
    }

    /**
     * @param GameResource $gameResource
     */
    public function setGameResource(GameResource $gameResource): void
    {
        $this->gameResource = $gameResource;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_BUY, self::TYPE_SELL])) {
            throw new \InvalidArgumentException("Invalid type");
        }

        $this->type = $type;
    }

    /**
     * @param Player $player
     * @param GameResource $gameResource
     * @param int $amount
     * @param int $price
     * @param string $type
     * @return MarketItem
     */
    public static function createForPlayer(Player $player, GameResource $gameResource, int $amount, int $price, string $type): MarketItem
    {
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
