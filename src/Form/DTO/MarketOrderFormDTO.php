<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Form\DTO;

class MarketOrderFormDTO
{
    public string $resource;
    public string $option;
    public int $price;
    public int $amount;

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getOption(): string
    {
        return $this->option;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
