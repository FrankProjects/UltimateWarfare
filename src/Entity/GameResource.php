<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * GameResource
 */
class GameResource
{
    const GAME_RESOURCE_CASH = 'cash';
    const GAME_RESOURCE_FOOD = 'food';
    const GAME_RESOURCE_WOOD = 'wood';
    const GAME_RESOURCE_STEEL = 'steel';

    /**
     * @param string $gameResource
     * @return bool
     */
    public static function isValid(string $gameResource): bool
    {
        if (in_array($gameResource, self::getAll())) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
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
