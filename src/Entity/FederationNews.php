<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class FederationNews
{
    private ?int $id;
    private int $timestamp;
    private string $news;
    private Federation $federation;

    public function getId(): int
    {
        return $this->id;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setNews(string $news): void
    {
        $this->news = $news;
    }

    public function getNews(): string
    {
        return $this->news;
    }

    public function getFederation(): Federation
    {
        return $this->federation;
    }

    public function setFederation(Federation $federation): void
    {
        $this->federation = $federation;
    }

    public static function createForFederation(Federation $federation, string $news): FederationNews
    {
        $federationNews = new FederationNews();
        $federationNews->setFederation($federation);
        $federationNews->setNews($news);
        $federationNews->setTimestamp(time());

        return $federationNews;
    }
}
