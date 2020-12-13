<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class GameResource
{
    public const GAME_RESOURCE_CASH = 'cash';
    public const GAME_RESOURCE_FOOD = 'food';
    public const GAME_RESOURCE_WOOD = 'wood';
    public const GAME_RESOURCE_STEEL = 'steel';

    public static function isValid(string $gameResource): bool
    {
        return in_array($gameResource, self::getAll(), true);
    }

    public static function getAll(): array
    {
        return [
            self::GAME_RESOURCE_CASH,
            self::GAME_RESOURCE_FOOD,
            self::GAME_RESOURCE_WOOD,
            self::GAME_RESOURCE_STEEL
        ];
    }
}
