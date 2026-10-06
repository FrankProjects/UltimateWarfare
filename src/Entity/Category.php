<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

enum Category: int
{
    case Announcements = 1;
    case General = 2;
    case Bugs = 3;
    case Ideas = 4;
    case OffTopic = 5;

    public function getTitle(): string
    {
        return match ($this) {
            self::Announcements => 'Announcements',
            self::General => 'General',
            self::Bugs => 'Bugs',
            self::Ideas => 'Ideas',
            self::OffTopic => 'Off-Topic',
        };
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::Announcements => 'announcements',
            self::General => 'general',
            self::Bugs => 'bugs',
            self::Ideas => 'ideas',
            self::OffTopic => 'off-topic',
        };
    }

    /**
     * @return Category[]
     */
    public static function getAll(): array
    {
        return self::cases();
    }
}
