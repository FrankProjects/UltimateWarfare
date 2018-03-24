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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param bool $type
     *
     * @return MarketItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set amount
     *
     * @param int $amount
     *
     * @return MarketItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set price
     *
     * @param int $price
     *
     * @return MarketItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return World
     */
    public function getWorld()
    {
        return $this->world;
    }

    /**
     * @param World $world
     */
    public function setWorld($world)
    {
        $this->world = $world;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    /**
     * @return GameResource
     */
    public function getGameResource()
    {
        return $this->gameResource;
    }

    /**
     * @param GameResource $gameResource
     */
    public function setGameResource($gameResource)
    {
        $this->gameResource = $gameResource;
    }

    /**
     * @return MarketItemType
     */
    public function getMarketItemType()
    {
        return $this->marketItemType;
    }

    /**
     * @param MarketItemType $marketItemType
     */
    public function setMarketItemType($marketItemType)
    {
        $this->marketItemType = $marketItemType;
    }
}
