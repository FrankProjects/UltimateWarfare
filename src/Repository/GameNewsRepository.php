<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameNews;

interface GameNewsRepository
{
    /**
     * @return GameNews[]
     */
    public function findActiveMainPageNews(): array;

    /**
     * @param GameNews $gameNews
     */
    public function remove(GameNews $gameNews): void;

    /**
     * @param GameNews $gameNews
     */
    public function save(GameNews $gameNews): void;
}
