<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Player;

class Notifications
{
    /**
     * @var bool
     */
    private $general = false;

    /**
     * @var bool
     */
    private $attacked = false;

    /**
     * @var bool
     */
    private $message = false;

    /**
     * @var bool
     */
    private $market = false;

    /**
     * @var bool
     */
    private $aid = false;

    /**
     * @var bool
     */
    private $news = false;

    /**
     * Set general
     *
     * @param bool $general
     */
    public function setGeneral(bool $general)
    {
        $this->general = $general;
    }

    /**
     * Get general
     *
     * @return bool
     */
    public function getGeneral(): bool
    {
        return $this->general;
    }

    /**
     * Set attacked
     *
     * @param bool $attacked
     */
    public function setAttacked(bool $attacked)
    {
        $this->attacked = $attacked;
    }

    /**
     * Get attacked
     *
     * @return bool
     */
    public function getAttacked(): bool
    {
        return $this->attacked;
    }

    /**
     * Set message
     *
     * @param bool $message
     */
    public function setMessage(bool $message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return bool
     */
    public function getMessage(): bool
    {
        return $this->message;
    }

    /**
     * Set market
     *
     * @param bool $market
     */
    public function setMarket(bool $market)
    {
        $this->market = $market;
    }

    /**
     * Get market
     *
     * @return bool
     */
    public function getMarket(): bool
    {
        return $this->market;
    }

    /**
     * Set aid
     *
     * @param bool $aid
     */
    public function setAid(bool $aid)
    {
        $this->aid = $aid;
    }

    /**
     * Get aid
     *
     * @return bool
     */
    public function getAid(): bool
    {
        return $this->aid;
    }

    /**
     * Set news
     *
     * @param bool $news
     */
    public function setNews(bool $news)
    {
        $this->news = $news;
    }

    /**
     * Get news
     *
     * @return bool
     */
    public function getNews(): bool
    {
        return $this->news;
    }
}
