<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Player;

class Notifications
{
    private bool $general = false;
    private bool $attacked = false;
    private bool $message = false;
    private bool $market = false;
    private bool $aid = false;
    private bool $news = false;

    public function setGeneral(bool $general): void
    {
        $this->general = $general;
    }

    public function getGeneral(): bool
    {
        return $this->general;
    }

    public function setAttacked(bool $attacked): void
    {
        $this->attacked = $attacked;
    }

    public function getAttacked(): bool
    {
        return $this->attacked;
    }

    public function setMessage(bool $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): bool
    {
        return $this->message;
    }

    public function setMarket(bool $market): void
    {
        $this->market = $market;
    }

    public function getMarket(): bool
    {
        return $this->market;
    }

    public function setAid(bool $aid): void
    {
        $this->aid = $aid;
    }

    public function getAid(): bool
    {
        return $this->aid;
    }

    public function setNews(bool $news): void
    {
        $this->news = $news;
    }

    public function getNews(): bool
    {
        return $this->news;
    }
}
