<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\MarketItem;
use FrankProjects\UltimateWarfare\Entity\World;

interface MarketItemRepository
{
    public function find(int $id): ?MarketItem;

    /**
     * @return MarketItem[]
     */
    public function findAll(): array;

    /**
     * @param World $world
     * @param string $type
     * @return MarketItem[]
     */
    public function findByWorldMarketItemType(World $world, string $type): array;

    public function remove(MarketItem $marketItem): void;
    
    public function save(MarketItem $marketItem): void;
}
