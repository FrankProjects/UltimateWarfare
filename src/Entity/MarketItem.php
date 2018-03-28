<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * MarketItem
 */
class MarketItem
{
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
     * @var MarketItemType
     */
    private $marketItemType;

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
    public function setAmount(int $amount)
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
    public function setPrice(int $price)
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
    public function setWorld(World $world)
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
    public function setPlayer(Player $player)
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
    public function setGameResource(GameResource $gameResource)
    {
        $this->gameResource = $gameResource;
    }

    /**
     * @return MarketItemType
     */
    public function getMarketItemType(): MarketItemType
    {
        return $this->marketItemType;
    }

    /**
     * @param MarketItemType $marketItemType
     */
    public function setMarketItemType(MarketItemType $marketItemType)
    {
        $this->marketItemType = $marketItemType;
    }
}
