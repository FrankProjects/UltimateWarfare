<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Enum;

enum GameUnitCategory: int
{
    case BUILDINGS = 1;
    case DEFENSE_BUILDINGS = 2;
    case SPECIAL_BUILDINGS = 3;
    case TROOPS = 6;
    case NAVAL_UNITS = 7;
    case AIR_UNITS = 8;
    case MISSILES = 9;

    public function getImageDir(): string
    {
        return 'units/';
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::BUILDINGS => 'Buildings',
            self::DEFENSE_BUILDINGS => 'Defense Buildings',
            self::SPECIAL_BUILDINGS => 'Special Buildings',
            self::TROOPS => 'Troops',
            self::NAVAL_UNITS => 'Naval Units',
            self::AIR_UNITS => 'Air Units',
            self::MISSILES => 'Missiles'
        };
    }

    public static function isValid(int $integer): bool
    {
        foreach (GameUnitCategory::getAll() as $gameUnitCategory) {
            if ($gameUnitCategory->value === $integer) {
                return true;
            }
        }

        return false;
    }

    public static function fromInteger(int $integer): ?GameUnitCategory
    {
        foreach (GameUnitCategory::getAll() as $gameUnitCategory) {
            if ($gameUnitCategory->value === $integer) {
                return $gameUnitCategory;
            }
        }

        return null;
    }

    /**
     * @return list<self>
     */
    public static function getAll(): array
    {
        return [
            self::BUILDINGS,
            self::DEFENSE_BUILDINGS,
            self::SPECIAL_BUILDINGS,
            self::TROOPS,
            self::NAVAL_UNITS,
            self::AIR_UNITS,
            self::MISSILES
        ];
    }

    /**
     * Leveled buildings are built once and then upgraded in levels (max 10) instead of
     * being stacked by amount. Their strength/durability is driven by level and health.
     */
    public function isLeveled(): bool
    {
        return in_array($this, [
            self::DEFENSE_BUILDINGS,
            self::SPECIAL_BUILDINGS,
        ], true);
    }

    public function isSendable(): bool
    {
        return in_array($this, [
            self::TROOPS,
            self::NAVAL_UNITS,
            self::AIR_UNITS,
            self::MISSILES
        ], true);
    }

    public function getConstructionAction(): string
    {
        return match ($this) {
            self::BUILDINGS, self::DEFENSE_BUILDINGS, self::SPECIAL_BUILDINGS,
            self::NAVAL_UNITS, self::AIR_UNITS, self::MISSILES => 'built',
            self::TROOPS => 'trained'
        };
    }

    public function getRemoveGameUnitActionDescription(): string
    {
        return match ($this) {
            self::BUILDINGS, self::DEFENSE_BUILDINGS, self::SPECIAL_BUILDINGS,
            self::NAVAL_UNITS, self::AIR_UNITS, self::MISSILES => 'destroyed',
            self::TROOPS => 'disbanded'
        };
    }
}
