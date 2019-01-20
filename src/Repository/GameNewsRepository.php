<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameNews;

interface GameNewsRepository
{
    /**
     * @param int $id
     * @return GameNews|null
     */
    public function find(int $id): ?GameNews;

    /**
     * @return GameNews[]
     */
    public function findAll(): array;

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
